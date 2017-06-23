<?php
namespace Deployer;
require 'recipe/common.php';
// Configuration
//set('repository', 'git@github.com:xishenma/wechat_api.git');
set('git_tty', true); // [Optional] Allocate tty for git on first deployment
set('shared_files', ['.env']);
set('shared_dirs', ['logs']);
set('writable_dirs', ['shared/logs']);
set('writable_mode', 'chmod');
set('keep_releases',20);
//set('default_stage', 'develop');
// Hosts
host('develop')
    ->hostname('39.108.7.175','39.108.163.234')
    ->stage('develop')
    ->set('repository','git@github.com:lyzhang1999/testdep.git')
    ->user('root')
    ->port(22)
    ->configFile('~/.ssh/config')
    ->identityFile('~/.ssh/id_rsa')
    ->forwardAgent(true)
    ->multiplexing(true)
    ->set('deploy_path', '/data/wwwroot/testdep')
    ->set('branch', 'master')
    ->set('bin/php', '/usr/local/php/bin/php');

set('watch_receive_location', function () {
    return (string)run('find /data/wwwroot -name "watch_receive.sh" -exec ls -1t "{}" + | head -1');
});

// Hosts
host('production')
    ->hostname('39.108.7.175')
    ->stage('production')
    ->set('repository','git@github.com:xishenma/wechat_api.git')
    ->user('root')
    ->port(22)
    ->configFile('~/.ssh/config')
    ->identityFile('~/.ssh/id_rsa')
    ->forwardAgent(true)
    ->multiplexing(true)
    ->set('deploy_path', '/data/wwwroot/wechat_api')
    ->set('branch', 'master')
    ->set('bin/php', '/usr/local/php/bin/php');


desc('Setup Crontab And Start Watch');
task('add_watch_receive_to_crontab', function () {
    run('echo "*/1 * * * * {{watch_receive_location}} start >> /data/wwwroot/script_monitor_watch.log 2>&1" > /var/spool/cron/root');
    run('{{watch_receive_location}} stop');
    run('{{watch_receive_location}} start');
    run("service php-fpm restart");
})->onHosts('production');
after('deploy:symlink', 'add_watch_receive_to_crontab');
desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);
// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
//my_test_here
