<?php

namespace GoodyTech\SyncBatch\Model;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParallelBatch extends Model {

    const SEQUENCE = "parallel_batch_seq";

    /** @var int プロセスを確認する間隔(秒) */
    const CHECK_INTERVAL_SECOND = 5;


    /**
     * @param \Closure $fnc
     * @return void
     * @throws \Exception
     */
    public static function runningParallelBatch(\Closure $fnc) {
        $master_id = ParallelBatch::getNewMasterSequence();
        $fnc($master_id);
        ParallelBatch::watiProcess($master_id);
    }

    /**
     * 管理番号取得
     * @return integer
     */
    private static function getNewMasterSequence(): int {
        return DB::query()->select(DB::raw("nextval('" . self::SEQUENCE . "')"))->first()->nextval;
    }

    /**
     * 実行中バッチ確認
     * @param $master_id
     * @return void
     * @throws \Exception
     */
    private static function watiProcess($master_id): void {
        $is_loop = true;
        $start_time = Carbon::now();
        $over_time_second = config('syncbatch.over_time_second',10);
        Log::info($over_time_second);
        while ($is_loop) {
            $now_time = Carbon::now();
            //実行時間の確認。設定値より超えている場合以上終了
            if ($now_time->diffInSeconds($start_time) >= $over_time_second) {
                throw new \Exception('Wait Process Overtime');
            }

            sleep(ParallelBatch::CHECK_INTERVAL_SECOND);//何秒ごとにチェックを行うか
            //実行中のタスク数確認
            $run_task_cnt = self::where('master_id', $master_id)->count();
            //全て終了している場合終了
            if ($run_task_cnt === 0) {
                $is_loop = false;
            }
        }
    }

    /**
     * 子プロセスの作成(並列で実行される)
     * @param int $master_id 親バッチID
     * @param string $child_cmd 子バッチsignature
     * @param string $prm 子バッチ引数
     */
    public static function createChildBatch(int $master_id, string $child_cmd, string $prm = ''): void {
        $child_batch = new self();
        $child_batch->master_id = $master_id;
        $child_batch->save();

        $cmd = 'nohup php '
            . base_path('artisan')
            . ' ' . $child_cmd
            . ' --process_id=' . $child_batch->id
            . ' ' . $prm
            . ' >/dev/null 2>&1 &';
        exec($cmd);
    }


    /**
     * 子バッチの終了
     * @param Command $child_cmd
     * @return void
     */
    public static function endChildProcess(Command $child_cmd): void {
        if ($child_cmd->hasOption('process_id')) {
            $process_id = $child_cmd->option('process_id');
            self::find($process_id)->delete();
        }
    }


}