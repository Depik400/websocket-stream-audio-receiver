<?php
declare(strict_types=1);

namespace Paulo\FileProcessorServer\Services;

/**
 * @template T
 */
abstract class ClassCacheFactory
{
    protected $fileName;

    final public function __construct()
    {
        if (!isset($this->fileName)) {
            throw new \RuntimeException("cache file name is not defined");
        }
    }

    private function folder(): string
    {
        $folderPath = base_path('bootstrap/cache/custom');
        if (!file_exists($folderPath) && !mkdir($folderPath) && !is_dir($folderPath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $folderPath));
        }
        return $folderPath;
    }

    private function getFileName(): string
    {
        return sprintf('%s/%s.php', $this->folder(), str_replace('.php', '', $this->fileName));
    }

    protected function getAllClasses(): array
    {
        return (require base_path('vendor/autoload.php'))->getClassMap();
    }

    /**
     * @param string       $classPath
     * @param class-string $classSting
     * @return bool
     */
    abstract protected function filterClassesToCache(string $classPath, string $classSting): bool;

    /**
     * @return class-string<T>[]
     */
    protected function getClassesToCache(): array
    {
        return array_keys(array_filter($this->getAllClasses(), [$this, 'filterClassesToCache'], ARRAY_FILTER_USE_BOTH));
    }

    public function createCacheFile(): string
    {
        $classes = $this->getClassesToCache();
        $path = $this->getFileName();
        if (file_exists($path)) {
            unlink($path);
        }
        file_put_contents($path, sprintf("<?php return %s;", var_export($classes, true)));
        return $path;
    }

    public static function makeCacheFile(): string
    {
        return (new static)->createCacheFile();
    }

    /**
     * @return class-string<T>[]
     */
    public function getCachedClasses(): array
    {
        $name = $this->getFileName();
        if (file_exists($name)) {
            return require $name;
        }
        return $this->getClassesToCache();
    }

    /**
     * @return class-string<T>[]
     */
    public static function getCache(): array
    {
        return (new static)->getCachedClasses();
    }
}