<?php

/**
 * Created by PhpStorm.
 * User: vitaliy
 * Date: 3/25/17
 * Time: 12:42 PM
 */

namespace AppBundle\Services;


class HostsManager
{

    private $folder_sites_available;
    private $folder_sites_enabled;

    /**
     * HostsManager constructor.
     * @param $folder_sites_available
     * @param $folder_sites_enabled
     */
    public function __construct($folder_sites_available, $folder_sites_enabled)
    {
        $this->folder_sites_available = $folder_sites_available;
        $this->folder_sites_enabled = $folder_sites_enabled;
    }


    public function getAvailableSites()
    {

        $files1 = scandir($this->folder_sites_available);


        $configs = [];
        foreach ($files1 as $config) {
            if (is_file($this->folder_sites_available . '/' . $config)) {
                $configs[] = $this->folder_sites_available . '/' . $config;
//                $configs[] = pathinfo($this->folder_sites_available . '/' . $config);
            }
        }

        return $configs;

    }

    public function getAvailableSitesDetailed()
    {


        $configs = $this->getAvailableSites();

        $detailed = [];
        foreach ($configs as $key => $config) {

            $content = file_get_contents($config);
            $directives = $this->getParsedConfig($config);
            $files = [];


            $folderAvailable = false;

            if (array_key_exists('DocumentRoot', $directives)) {

                foreach ($directives['DocumentRoot'] as $projectFolder) {
                    if (is_dir($projectFolder)) {
                        $files[$projectFolder] = [
                            'size' => null,
//                            'size' => $this->getFolderSize($projectFolder),
                            'error' => null,
                            'files' => scandir($projectFolder)
                        ];
                        $folderAvailable = true;
                    } else {
                        $files[$projectFolder] = [
                            'size' => null,
                            'files' => [],
                            'error' => 'Folder not exist'
                        ];
                    }
                }

            }

            if (array_key_exists('ServerName', $directives)) {


                $detailed[$directives['ServerName']['0']] = [
                    'configFile' => $config,
                    'content' => $content,
                    'config' => $directives,
                    'files' => $files,
                    'folderAvailable' => $folderAvailable,
                    'key' => $key

                ];
            }
        }


        return $detailed;

    }


    private function getParsedConfig($virtualHostFilename, $withPrefix = true)
    {


        $content = file_get_contents($virtualHostFilename);


        $neededDirectives = [
            'CustomLog',
            'ErrorLog',
            'ServerName',
            'ServerAlias',
            'DocumentRoot',
            'SSLCertificateFile',
            'SSLCertificateKeyFile',
            'SSLCertificateChainFile'
        ];


        $directives = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            foreach ($neededDirectives as $directive) {
                if (strpos($line, $directive)) {

                    if (substr(trim($line), 0, 1) == "#") {
                        continue;
                    }

                    if ($directive == 'DocumentRoot') {
                        $line = str_replace('"', '', $line);
                    }
                    $directives[$directive][] = trim(str_replace($directive, '', $line));
                }
            }
        }


        foreach ($directives as &$directive) {
            $directive = array_unique($directive);
        }


        return $directives;

    }


    private function findDirective($directive, $content)
    {
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (strpos($line, $directive)) {


                return $directive . ': ' . trim(str_replace($directive, '', $line));
            }
        }
        return null;

    }


    private function  getFolderSize($f)
    {

        $io = popen('/usr/bin/du -sh ' . $f, 'r');
        $size = fgets($io, 4096);
        $size = substr($size, 0, strpos($size, "\t"));
        pclose($io);
        return $size;
    }


}