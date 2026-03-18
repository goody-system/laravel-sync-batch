親バッチ
```
ParallelBatch::runningParallelBatch(function ($master_id) {
            //子バッチ
            ParallelBatch::createChildBatch(
                $master_id
                , 'app:child'
                , '--prm1=500 --prm2=200'
            );

            ParallelBatch::createChildBatch(
                $master_id
                , 'app:child'
                , '--prm1=501 --prm2=201'
            );

        });
```

子バッチ

```
protected $signature = 'app:child {--process_id=}';

    public function handle()
    {
        try {
            /**
             * your process
             */

           


            /**
             * your process end
             */
        } finally {
            ParallelBatch::endChildProcess($this);
        }
    }
```
