<?php
declare(strict_types=1);

function out($label, $value): void {
    if (is_array($value) || is_object($value)) $value = json_encode($value, JSON_PRETTY_PRINT);
    echo "== {$label} ==\n{$value}\n\n";
}

header('Content-Type: text/plain; charset=utf-8');

out('SAPI', PHP_SAPI);
out('PHP_VERSION', PHP_VERSION);
out('UID/GID', sprintf("uid=%d gid=%d", posix_geteuid(), posix_getegid()));
out('ini_loaded_file', php_ini_loaded_file() ?: '(none)');
out('extension_dir', ini_get('extension_dir'));
out('LD_LIBRARY_PATH', getenv('LD_LIBRARY_PATH') ?: '(not set)');
out('open_basedir', ini_get('open_basedir') ?: '(not set)');

$paths = [
    '/usr/lib/aarch64-linux-gnu/libvips.so.42',
    '/lib/aarch64-linux-gnu/libvips.so.42',
];

foreach ($paths as $p) {
    out("file_check {$p}", sprintf(
        "exists=%s readable=%s realpath=%s",
        file_exists($p) ? 'yes' : 'no',
        is_readable($p) ? 'yes' : 'no',
        file_exists($p) ? (realpath($p) ?: '(realpath failed)') : '(n/a)'
    ));
}

out('extension_loaded(vips)', extension_loaded('vips') ? 'yes' : 'no');
out('extension path (ini_get)', ini_get('extension_dir') . '/vips.so (may vary)');

out('get_loaded_extensions', get_loaded_extensions());

/**
 * PROBE A: dlopen libvips via libdl to see the REAL dlerror()
 */
if (class_exists('FFI')) {
    try {
        $cdef = "void* dlopen(const char* filename, int flag);\nconst char* dlerror(void);\nint dlclose(void* handle);\n";
        $dl = FFI::cdef($cdef, "libdl.so.2");
        $RTLD_LAZY = 0x00001;

        foreach (['/usr/lib/aarch64-linux-gnu/libvips.so.42', '/lib/aarch64-linux-gnu/libvips.so.42', 'libvips.so.42'] as $name) {
            $h = $dl->dlopen($name, $RTLD_LAZY);
            if ($h == null) {
                $err = FFI::string($dl->dlerror());
                out("dlopen {$name}", "FAIL: {$err}");
            } else {
                out("dlopen {$name}", "OK");
                $dl->dlclose($h);
            }
        }
    } catch (Throwable $e) {
        out('FFI dlopen probe exception', $e->getMessage());
    }
} else {
    out('FFI available', 'no');
}

/**
 * PROBE B: ldd on libvips itself (this is the #1 way to find the “real” missing .so)
 */
$cmd = "ldd /usr/lib/aarch64-linux-gnu/libvips.so.42 2>&1";
$ldd = shell_exec($cmd);
out('ldd(libvips.so.42)', $ldd ?: '(shell_exec disabled or no output)');

/**
 * PROBE C: Try calling a vips function (if your extension exposes one)
 * Adjust depending on extension API.
 */
try {
    if (extension_loaded('vips')) {
        // If you have a known call, put it here.
        // out('vips_version', \Vips\version(0));  // example if php-vips ext provides it
        out('vips loaded', 'yes (no function called in probe)');
    }
} catch (Throwable $e) {
    out('vips call exception', $e->getMessage());
}

out('DONE', 'If dlopen fails, the dlerror text is your smoking gun (often a missing dependency).');

