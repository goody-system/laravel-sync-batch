<?php

namespace GoodyTech\SyncBatch\Model;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class ParallelBatch extends Model {

    const SEQUENCE = "parallel_batch_seq";

    /**
     * @param \Closure $fnc
     * @return void
     */
    public static function runningParallelBatch(\Closure $fnc){
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
     */
    private static function watiProcess($master_id): void {
        $is_loop = true;
        while ($is_loop) {
            sleep(5);
            $run_task_cnt = self::where('master_id', $master_id)->count();
            if ($run_task_cnt === 0) {
                $is_loop = false;
            }
        }
    }

    /**
     * 子プロセスの作成
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
            .' --process_id=' . $child_batch->id
            .' '.$prm
            .' >/dev/null 2>&1 &';
        exec($cmd);
    }


    /**
     * 子バッチの終了
     * @param Command $child_cmd
     * @return void
     */
    public static function endChildProcess(Command $child_cmd): void {
        if($child_cmd->hasOption('process_id')) {
            $process_id = $child_cmd->option('process_id');
            self::find($process_id)->delete();
        }
    }


}