<?php

class W3_MinifyFileTool
{
    private $document_root = '';


    /**
     * Devuelve TRUE si el fichero existe en disco, o si se trata de una URL
     * con un código de estado 200.
     *
     * @param string $file
     * @return bool
     */
    public function fileExists($file)
    {

        $path = $this->document_root . '/' . $file;
        $exist = false;

        if (file_exists($path)) $exist = true;

        if (!$exist) {

            $exist = $this->checkByHttp($file);
        }

        return $exist;
    }


    /**
     * Devuelve el path de un fichero. Si se trata de una URL devuelve la ruta completa.
     *
     * @param string $file
     * @return bool|string
     */
    public function realPath($file)
    {
        $path = $this->document_root . '/' . $file;

        $realpath = realpath($path);

        if ($realpath === false) {

            if ($this->checkByHttp($file)) {

                $realpath = $this->getHttpPath($file);

            } else {

                $realpath = false;
            }
        }

        return $realpath;
    }


    /**
     *
     * Agrega URL de wordpress al array de directorios permitidos para la minificación.
     *
     * @param string[] $allowDirs
     * @return string[]
     */
    public function allowDirs($allowDirs)
    {
        $site_dir = array($_ENV['WP_HOME']);
        return array_merge($allowDirs, $site_dir);
    }


    /**
     * Comprueba que el fichero exista y que sea un fichero regular. En el caso de recibir una url
     * comprueba que el código de estado devuelvo sea 200.
     *
     * @param string $file
     * @return bool
     */
    public function isFile($file)
    {
        $exist = is_file($file);

        if (!$exist) {

            $exist = $this->checkHttpPath($file);
        }

        return $exist;
    }


    /**
     * Obtiene el document root para un path dado. Si se trata de una URL, el document root
     * será el baseurl (variable de entorno WP_HOME). Si no se trata de una URL, devuelve el document
     * root por default.
     *
     * @param string $path
     * @return string
     */
    public function getRealDocumentRoot($path)
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $realDocumentRoot = $_ENV['WP_HOME'];
        } else {
            $realDocumentRoot = $this->document_root;
        }

        return $realDocumentRoot;
    }


    /**
     * Obtiene la última fecha de modificación de un fichero. Si se trata de una url, la obtiene de
     * la cabecera Last-Modified.
     *
     * @param string $file
     * @return int
     */
    public function getFileMtime($file)
    {
        if (filter_var($file, FILTER_VALIDATE_URL)) {

            $headers = @get_headers($file, 1);

            if (isset($headers['Last-Modified'])) {
                $time = strtotime($headers['Last-Modified']);
            } else {
                $time = time();
            }

        } else {

            $time = filemtime($file);
        }

        return $time;
    }

    public function setDocumentRoot($document_root)
    {
        $this->document_root = $document_root;
    }

    /**
     * Comprueba si el fichero pasado por parámetro es una url válida del dominio actual
     *
     * @param string $file
     * @return bool
     */
    private function checkByHttp($file)
    {
        $url = $this->getHttpPath($file);

        return $this->checkHttpPath($url);
    }

    /**
     * Obtiene la url completa del recurso pasado por parámetro
     *
     * @param string $file
     * @return string
     */
    private function getHttpPath($file)
    {
        return $_ENV['WP_HOME']. '/' .$file;
    }


    /**
     * Comprueba que la url devuelva un código de estado 200.
     *
     * @param string $url
     * @return bool
     */
    private function checkHttpPath($url)
    {
        $headers = @get_headers($url);

        $exist = false;
        if (strpos($headers[0],'200') !==false) $exist = true;

        return $exist;
    }



}