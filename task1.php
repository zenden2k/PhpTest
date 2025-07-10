<?php
const REDIS_HOST = '127.0.0.1';
const REDIS_PORT = 6379;
const REDIS_AUTH = false;
const REDIS_PASSWORD = '';

$redisKey = md5(__FILE__);

$redis = new Redis();
try {
    $redis->connect(REDIS_HOST, REDIS_PORT);
    if(REDIS_AUTH) {
        $redis->auth(REDIS_PASSWORD);
    }
    // Устанавливаем значение по ключу и получаем предыдущее значение
    if ($redis->getSet($redisKey, 1) !== false) {
        echo "Script is already running." . PHP_EOL;
        exit(1);
    }
} catch(RedisException $e) {
    printf("Redis error: %s". PHP_EOL, $e->getMessage());
    exit(2);
}
// Регистрируем обработчик выхода из скрипта на случвай, если скрипт прервётся по какой-то причине
register_shutdown_function(function() use ($redis, $redisKey) {
    try {
        $redis->del($redisKey);
    } catch(RedisException $e) {
        printf("Redis error: %s" . PHP_EOL, $e->getMessage());
    }
});
echo "Waiting 5 seconds..." . PHP_EOL;
sleep(5);
echo "Finished." . PHP_EOL;





