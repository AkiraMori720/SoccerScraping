<?php
/**
 * Created by PhpStorm.
 * User: Yuan
 * Date: 6/16/2020
 * Time: 1:36 AM
 */


if(!function_exists('log_to_file')) {
    function log_to_file($data, $toJson = true)
    {
        try {
            $data['ip'] = get_client_ip();

            $fileName = "log_" . getDateTime('Y_m_d') . ".txt";
            if ($toJson) {
                $data['log_time'] = getDateTime();
            }

            $myFile = fopen(dirname(__DIR__) . "/logs/" . $fileName, "a");
            if ($myFile) {
                fwrite($myFile, ($toJson ? json_encode($data, JSON_PRETTY_PRINT) : $data) . PHP_EOL);
                fclose($myFile);
            }
        } catch (Exception $e) {
            ;
        }
    }
}

if(!function_exists('is_pdf')) {
    function is_pdf($file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return in_array($ext, array('pdf'));
    }
}

if(!function_exists('is_excel')) {
    function is_excel($file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return in_array($ext, array('xls', 'xlsx'));
    }
}

if(!function_exists('is_word')) {
    function is_word($file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        return in_array($ext, array('doc', 'docx'));
    }
}

if(!function_exists('writeStreamToFile')) {
    function writeStreamToFile($path, $data)
    {
        try {
            $source = fopen($data, 'r');
            $destination = fopen($path, 'w');

            stream_copy_to_stream($source, $destination);

            fclose($source);
            fclose($destination);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}

if(!function_exists('writeToFile')) {
    function writeToFile($path, $data)
    {
        try {
            $handler = fopen($path, 'wb');
            if ($handler) {
                fwrite($handler, $data);
                fclose($handler);
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}

if(!function_exists('saveImageLinkToFile')) {
    function saveImageLinkToFile($link, $path, $name = '')
    {
        $fileName = null;
        try {
            createDIR($path);
            $orgName = pathinfo($link, PATHINFO_FILENAME);
            $ext = pathinfo($link, PATHINFO_EXTENSION);

            $fileName = (isEmptyString($name) ? $orgName : $name) . ".{$ext}";
            $filePath = $path . "/" . $fileName;

            if(file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }

            file_put_contents($filePath, file_get_contents($link));
        }
        catch (Exception $e) {
            $fileName = null;
        }

        return $fileName;
    }
}

if(!function_exists('extractZipFile')) {
    function extractZipFile($zipFile, $toPath)
    {
        $zip = new ZipArchive;
        $res = $zip->open($zipFile);
        if ($res === TRUE) {
            // extract it to the path we determined above
            $zip->extractTo($toPath);
            $zip->close();

            return true;
        }

        return false;
    }
}

if(!function_exists('searchTree')) {
    /**
     * ------------------------------------------------------------------------
     *  searchTree :
     * ========================================================================
     *
     *
     * @param $dir
     * @param $searchExts - Search Extensions
     * @param int $curDepth
     * @param int $maxDepth
     * @param bool $fileMode
     * @return array
     *
     * ------------------------------------------------------------------------
     */
    function searchTree($dir, $searchExts, $curDepth = 1, $maxDepth = 1000, $fileMode = true)
    {
        if ($curDepth > $maxDepth) {
            return array();
        }

        $files = array_diff(scandir($dir), array('.', '..'));

        $results = array();
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $data = searchTree($path, $searchExts, $curDepth + 1, $maxDepth, $fileMode);

                if (sizeof($data) > 0) {
                    if ($fileMode) {
                        $results = array_unique(array_merge($results, $data));
                    } else {
                        $results[$file] = $data;
                    }
                }
            } else {
                if ($searchExts != null && is_array($searchExts)) {
                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                    if (in_array($ext, $searchExts)) {
                        $results[] = $path;
                    }
                } else {
                    $results[] = $path;
                }
            }
        }
        return $results;
    }
}

if(!function_exists('delTree')) {
    function delTree($dir)
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            exec(sprintf("rd /s /q %s", escapeshellarg($dir)));
        } else {
            exec(sprintf("rm -rf %s", escapeshellarg($dir)));
        }
    }
}

if(!function_exists('createDIR')) {
    function createDIR($path, $permission=0777, $recursive = TRUE)
    {
        $error = '';
        if(!file_exists($path) || !is_dir($path)) {
            if (!mkdir($path, $permission, $recursive)) {
                $error = error_get_last();
            }
        }

        return $error;
    }
}


if(!function_exists('executeShellCommand')) {
    function executeShellCommand($command, $toJSON = true, $assoc = TRUE)
    {
        $response = null;
        if(!isEmptyString($command)) {
            $output = shell_exec($command);

            if($toJSON) {
                $response = json_decode($output, true);
            }
            else {
                $response = $output;
            }
        }

        return $response;
    }
}
