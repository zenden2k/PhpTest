<?php
const REDIS_HOST = '127.0.0.1';
const REDIS_PORT = 6379;
const REDIS_AUTH = false;
const REDIS_PASSWORD = '';
const WAIT_TIMEOUT = 5; // 5 секунд
const LOCK_TTL = WAIT_TIMEOUT * 60 * 2;
$redisKey = md5(__FILE__);

$redis = new Redis();
try {
    $redis->connect(REDIS_HOST, REDIS_PORT);
    if(REDIS_AUTH) {
        $redis->auth(REDIS_PASSWORD);
    }

    // Устанавливаем значение по ключу, только если оно не существует
    // с заданным временем жизни
    if ($redis->set($redisKey, 1, ['nx', 'ex' => LOCK_TTL]) === false) {
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
printf("Waiting %d seconds...". PHP_EOL, WAIT_TIMEOUT);
sleep(WAIT_TIMEOUT);
echo "Finished." . PHP_EOL;





