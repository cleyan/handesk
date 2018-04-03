<?php
    namespace Deployer;
    
    require 'recipe/laravel.php';

// Project name
    set('application', 'soporte');

// Project repository
    set('repository', 'git@github.com:cleyan/handesk.git');

// [Optional] Allocate tty for git clone. Default value is false.
    set('git_tty', true);

// Shared files/dirs between deploys
    add('shared_files', ['.env']);
    add('shared_dirs', []);

// Writable dirs by web server
    add('writable_dirs', []);
    set('allow_anonymous_stats', false);

// Configurations
    set('composer_options', 'install --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader');

// Hosts
    
    host('ec2-35-171-86-212.compute-1.amazonaws.com')
        ->stage('production')
        ->user('ubuntu')
        ->set('branch', 'master')
        ->set('deploy_path', '/var/www/{{application}}');

// Tasks
    
    task('upload:env', function () {
        upload('.env.test', '{{deploy_path}}/shared/.env');
    })->onStage('test');
    
    task('upload:env', function () {
        upload('.env.production', '{{deploy_path}}/shared/.env');
    })->onStage('production');
    
    task('reload:php-fpm', function () {
        run('sudo systemctl restart php7.1-fpm.service');
    });
    
    desc('Execute artisan config:clear');
    task('artisan:config:clear', function () {
        run('{{bin/php}} {{release_path}}/artisan config:clear');
    });
    
    desc('Sobreescribe optimize');
    task('artisan:optimize', function () {
        run('{{bin/php}} {{release_path}}/artisan --version');
    });



// [Optional] if deploy fails automatically unlock.
    after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
    
    before('deploy:symlink', 'artisan:migrate');
    before('deploy:shared', 'upload:env');
    after('artisan:config:cache', 'artisan:config:clear');
    after('deploy', 'reload:php-fpm');
