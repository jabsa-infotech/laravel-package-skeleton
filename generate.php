<?php

class PackageGenerator
{
    private string $baseDir;
    private array $replacements = [];

    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    public function generate(
        string $vendorName,
        string $packageName,
        string $authorName,
        string $authorEmail,
        string $description = 'A Laravel package'
    ): void {
        // Setup replacements
        $packageClass = $this->studly($packageName);
        $this->replacements = [
            '{vendor}' => $vendorName,
            '{package_name}' => $packageName,
            '{package_class}' => $packageClass,
            '{author_name}' => $authorName,
            '{author_email}' => $authorEmail,
            '{description}' => $description,
        ];

        // Create target directory
        $baseDir = dirname($this->baseDir);
        $baseDir = ".";
        $targetDir = "{$baseDir}/packages/{$vendorName}/{$packageName}";
        
        if (is_dir($targetDir)) {
            throw new RuntimeException("Package directory already exists: {$targetDir}");
        }

        $this->copyStubs($targetDir);
        $this->initGitRepo($targetDir);
        
        echo "Package created successfully at: {$targetDir}\n";
    }

    private function copyStubs(string $targetDir): void
    {
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $stubsDir = $this->baseDir . '/stubs';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($stubsDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            // Get relative path from stubs directory
            $relativePath = substr($item->getPathname(), strlen($stubsDir) + 1);
            
            if ($item->isDir()) {
                $newPath = $targetDir . '/' . $relativePath;
                if (!is_dir($newPath)) {
                    mkdir($newPath, 0755, true);
                }
            } else {
                // Remove .stub extension and replace placeholders
                $newPath = $targetDir . '/' . str_replace('.stub', '', $relativePath);
                
                // Replace placeholders in filepath
                foreach ($this->replacements as $search => $replace) {
                    $newPath = str_replace($search, $replace, $newPath);
                }
                
                // Ensure target directory exists
                $newDir = dirname($newPath);
                if (!is_dir($newDir)) {
                    mkdir($newDir, 0755, true);
                }
                
                // Copy and replace content
                $content = file_get_contents($item->getPathname());
                $content = $this->replaceContent($content);
                file_put_contents($newPath, $content);
            }
        }
    }

    private function replaceContent(string $content): string
    {
        return str_replace(
            array_keys($this->replacements),
            array_values($this->replacements),
            $content
        );
    }

    private function initGitRepo(string $targetDir): void
    {
        chdir($targetDir);
        exec('git init');
        exec('git add .');
        exec('git commit -m "Initial commit"');
    }

    private function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
    }
}

// CLI interface
if (php_sapi_name() === 'cli') {
    $generator = new PackageGenerator(__DIR__);
    
    echo "Jabsa Laravel Package Generator\n";
    echo "=======================\n\n";
    
    $vendorName = readline("Vendor name (e.g. acme): ");
    $packageName = readline("Package name (e.g. awesome-package): ");
    $authorName = readline("Author name: ");
    $authorEmail = readline("Author email: ");
    $description = readline("Package description [A Laravel package]: ") ?: 'A Laravel package';
    
    try {
        $generator->generate(
            $vendorName,
            $packageName,
            $authorName,
            $authorEmail,
            $description
        );
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}