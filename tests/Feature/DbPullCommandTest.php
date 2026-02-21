<?php

use Illuminate\Support\Facades\Process;

it('dumps production database to a file', function () {
    Process::fake([
        'kubectl exec -n airbnb-tool*' => Process::result(output: 'airbnb_tool:secret123'),
        'kubectl exec -n mariadb*' => Process::result(output: '-- MariaDB dump'),
    ]);

    $dumpPath = database_path('dump.sql');

    if (file_exists($dumpPath)) {
        unlink($dumpPath);
    }

    $this->artisan('db:pull', ['--dump-only' => true])
        ->expectsOutputToContain('Fetching production database credentials')
        ->expectsOutputToContain('Dumping production database')
        ->expectsOutputToContain('Dump saved to')
        ->assertSuccessful();

    expect(file_exists($dumpPath))->toBeTrue();
    expect(trim(file_get_contents($dumpPath)))->toBe('-- MariaDB dump');

    unlink($dumpPath);

    Process::assertRan(fn ($process) => str_contains($process->command, 'kubectl exec -n airbnb-tool'));
    Process::assertRan(fn ($process) => str_contains($process->command, 'kubectl exec -n mariadb'));
});

it('fails when credentials cannot be retrieved', function () {
    Process::fake([
        'kubectl exec -n airbnb-tool*' => Process::result(exitCode: 1, errorOutput: 'connection refused'),
    ]);

    $this->artisan('db:pull', ['--dump-only' => true])
        ->expectsOutputToContain('Could not retrieve production database credentials')
        ->assertFailed();
});

it('fails when dump command fails', function () {
    Process::fake([
        'kubectl exec -n airbnb-tool*' => Process::result(output: 'airbnb_tool:secret123'),
        'kubectl exec -n mariadb*' => Process::result(exitCode: 1, errorOutput: 'Access denied'),
    ]);

    $this->artisan('db:pull', ['--dump-only' => true])
        ->expectsOutputToContain('Failed to dump production database')
        ->assertFailed();
});

it('warns when local database is not mysql or mariadb', function () {
    Process::fake([
        'kubectl exec -n airbnb-tool*' => Process::result(output: 'airbnb_tool:secret123'),
        'kubectl exec -n mariadb*' => Process::result(output: '-- MariaDB dump'),
    ]);

    $dumpPath = database_path('dump.sql');

    $this->artisan('db:pull')
        ->expectsOutputToContain('Automatic import only works with MySQL/MariaDB')
        ->assertSuccessful();

    if (file_exists($dumpPath)) {
        unlink($dumpPath);
    }
});
