<?php

declare(strict_types=1);

namespace SPC\builder\linux\library;

use SPC\exception\FileSystemException;
use SPC\exception\RuntimeException;
use SPC\store\FileSystem;

class libxml2 extends LinuxLibraryBase
{
    public const NAME = 'libxml2';

    /**
     * @throws RuntimeException
     * @throws FileSystemException
     */
    public function build(): void
    {
        $enable_zlib = $this->builder->getLib('zlib') ? 'ON' : 'OFF';
        $enable_icu = $this->builder->getLib('icu') ? 'ON' : 'OFF';
        $enable_xz = $this->builder->getLib('xz') ? 'ON' : 'OFF';

        [, , $destdir] = SEPARATED_PATH;

        FileSystem::resetDir($this->source_dir . '/build');
        shell()->cd($this->source_dir . '/build')
            ->exec(
                "{$this->builder->configure_env} " . ' cmake ' .
                "{$this->builder->makeCmakeArgs()} " .
                '-DBUILD_SHARED_LIBS=OFF ' .
                '-DLIBXML2_WITH_ICONV=ON ' .
                '-DIconv_IS_BUILT_IN=OFF ' .
                "-DLIBXML2_WITH_ZLIB={$enable_zlib} " .
                "-DLIBXML2_WITH_ICU={$enable_icu} " .
                "-DLIBXML2_WITH_LZMA={$enable_xz} " .
                '-DLIBXML2_WITH_PYTHON=OFF ' .
                '-DLIBXML2_WITH_PROGRAMS=OFF ' .
                '-DLIBXML2_WITH_TESTS=OFF ' .
                '..'
            )
            ->exec("cmake --build . -j {$this->builder->concurrency}")
            ->exec("make install DESTDIR={$destdir}");

        if (is_dir(BUILD_INCLUDE_PATH . '/libxml2/libxml')) {
            if (is_dir(BUILD_INCLUDE_PATH . '/libxml')) {
                shell()->exec('rm -rf "' . BUILD_INCLUDE_PATH . '/libxml"');
            }
            $path = FileSystem::convertPath(BUILD_INCLUDE_PATH . '/libxml2/libxml');
            $dst_path = FileSystem::convertPath(BUILD_INCLUDE_PATH . '/');
            shell()->exec('mv "' . $path . '" "' . $dst_path . '"');
        }
    }
}
