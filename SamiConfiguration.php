<?php

namespace Carew\Plugin\Sami;

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

class SamiConfiguration
{
    private $projectDir;
    private $srcDir;
    private $name;
    private $branches;
    private $buildDir;

    public function __construct($projectDir, $srcDir, $name, array $branches, $buildDir)
    {
        $this->projectDir = $projectDir;
        $this->srcDir     = $srcDir;
        $this->name       = $name;
        $this->branches   = $branches;
        $this->buildDir   = $buildDir;
    }

    public function getConfiguration()
    {
        if (!file_exists($this->projectDir)) {
            throw new \Exception(sprintf('Directory "%s" does no exists', $this->projectDir));
        }
        $this->projectDir = realpath($this->projectDir);

        $samiCache = getenv('HOME').'/.sami';
        if (!is_dir($samiCache) && ! @mkdir($samiCache, 0777, true)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" does not exist and could not be created.',
                $samiCache
            ));
        }

        if (!is_writable($samiCache)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" is not writable.',
                $samiCache
            ));
        }

        $projectCachePath     = $samiCache.'/'.str_replace('/', '-', $this->projectDir);
        $projectSrcCachePath  = $projectCachePath.'/src';
        $projectSamiCachePath = $projectCachePath.'/cache';
        $git_cmd = function($cmd) use ($projectSrcCachePath) {
            return exec(sprintf(
                'git --git-dir=%s --work-tree=%s %s >/dev/null 2>&1',
                $projectSrcCachePath.'/.git',
                $projectSrcCachePath,
                $cmd
            ));
        };

        if (!file_exists($projectSrcCachePath)) {
            exec(sprintf('git clone %s %s', $this->projectDir, $projectSrcCachePath));
        } else {
            $git_cmd('fetch');
        }

        foreach (array_keys($this->branches) as $branch) {
            $git_cmd('branch '.$branch);
            $git_cmd('checkout '.$branch);
            $git_cmd('reset --hard origin/'.$branch);
        }

        $iterator = Finder::create()
            ->files()
            ->name('*.php')
            ->exclude('Tests')
            ->in($projectSrcCachePath.'/'.$this->srcDir)
        ;

        $versions = GitVersionCollection::create($projectSrcCachePath);
        foreach ($this->branches as $key => $value) {
            $versions->add($key, $value);
        }

        return new Sami($iterator, array(
            'theme'                => 'lyrixx',
            'versions'             => $versions,
            'title'                => $this->name,
            'build_dir'            => $this->buildDir.'/%version%',
            'cache_dir'            => $projectSamiCachePath.'/%version%',
            'template_dirs'        => array(__DIR__.'/themes/lyrixx'),
            'default_opened_level' => 2,
        ));
    }
}
