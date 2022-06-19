<?php
declare(strict_types=1);

namespace Mgrn\Scoperender;

class ScopeRenderer
{

    protected string $defaultRendererExtension = 'phtml';
    protected string $renderRootFolder;
    protected BasicEscaper $escaper;

    private string $runFile;

    public function __construct(string $renderRootFolder, string $defaultRendererExtension = 'phtml')
    {
        $this->assertRenderRootFolder($renderRootFolder);
        $this->defaultRendererExtension = $defaultRendererExtension;
        $this->renderRootFolder = realpath($renderRootFolder);
        $this->escaper = new BasicEscaper();
    }

    /**
     * returns echoed or output content as a string.
     * does not modify buffered contents.
     *
     * @param string $renderFile File to render. Must include path relative from render root.
     * @param ScopeInterface $scope
     * @return string
     * @throws Exception
     */
    public function render(string $renderFile, ScopeInterface $scope, ?string $alternateRenderExtention=null): string
    {
        if (!$scope->hasEscaper()) {
            if (is_null($this->escaper)) {
                throw new Exception('Neither renderer nor scope has escaper set');
            }
            $scope->setEscaper($this->escaper); // default it
        }
        $outstanding = ob_get_contents();
        if ($outstanding!==false) {
            ob_clean();
        }
        $startedWithObOn = $outstanding===false ? false : true;
        $outstanding = empty($outstanding) ? '' : $outstanding;
        ob_start();

        $targetFile = $renderFile;
        if ($alternateRenderExtention) {
            $targetFile .= '.'.$alternateRenderExtention;
        }
        elseif ($this->defaultRendererExtension) {
            $targetFile .= '.'.$this->defaultRendererExtension;
        }
        if (substr($targetFile,0,1)== DIRECTORY_SEPARATOR) {
            $targetFile = substr($targetFile,1);
        }
        $targetFile = $this->renderRootFolder.DIRECTORY_SEPARATOR.$targetFile;
        $this->assertResourceContainedInFolder($this->renderRootFolder,$targetFile);

        $scope->scope($targetFile);

        $scoped = ob_get_contents();
        if ($scoped === false) {
            $scoped = "";
        }
        if ($startedWithObOn) {
            ob_clean();
            echo $outstanding; // put it back in the higher buffered scope, before the return
        }
        else {
            ob_end_clean();
        }

        return $scoped;
    }

    public function setEscaper(EscaperInterface $escaper): ScopeRenderer
    {
        $this->escaper = $escaper;
        return $this;
    }

    /**
     * Allows the 'root' containing folder for the renderer to be reset. The renderer will not render any partial
     * or file found outside of this root folder.
     *
     * @param string $renderRootFolder
     * @return void
     * @throws Exception
     */
    public function setRenderRootFolder(string $renderRootFolder): void
    {
        $this->assertRenderRootFolder($renderRootFolder);
        $this->renderRootFolder = realpath($renderRootFolder);
    }

    /**
     * Basic method to handle try/catch and turn assertResourceContainedInFolder into a boolean call
     *
     * @param string $containingFolder
     * @param string $resourceTarget
     * @return bool
     */
    public function isResourceContainedInFolder(string $containingFolder, string $resourceTarget): bool
    {
        try {
            $this->assertResourceContainedInFolder($containingFolder,$resourceTarget);
            return true;
        }
        catch (Exception $e) {}
        return false;
    }

    /**
     * Performs a basic test to confirm a resource is actually contained within another folder.
     *
     * @param string $containingFolder
     * @param string $resourceTarget
     * @return void
     * @throws Exception
     */
    public function assertResourceContainedInFolder(string $containingFolder, string $resourceTarget): void
    {
        if (!file_exists($containingFolder) || !is_dir($containingFolder)) {
            throw new Exception('Containing folder not found or is not directory');
        }
        if (!file_exists($resourceTarget)) {
            throw new Exception('Resource target not found');
        }
        $containingFolder = realpath($containingFolder);
        $resourceTarget = realpath($resourceTarget);
        if ($containingFolder === false || $resourceTarget === false) {
            throw new Exception('Filesystem permission denied on resource');
        }
        if (!str_starts_with($resourceTarget, $containingFolder)) {
            throw new Exception('Resource target is not contained in containing folder');
        }
    }

    /**
     * Asserts a resource is a directory and is accessible. Not a public method.
     *
     * @param string $renderRootFolder
     * @return void
     * @throws Exception
     */
    private function assertRenderRootFolder(string $renderRootFolder): void
    {
        if (!file_exists($renderRootFolder) || !is_dir($renderRootFolder)) {
            throw new Exception('Render root folder not found or is not directory');
        }
        $renderRootFolder = realpath($renderRootFolder);
        if ($renderRootFolder === false) {
            throw new Exception('Filesystem permission denied on render root folder');
        }
    }
}