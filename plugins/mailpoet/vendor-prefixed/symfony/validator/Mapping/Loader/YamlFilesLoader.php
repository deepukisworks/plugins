<?php
namespace MailPoetVendor\Symfony\Component\Validator\Mapping\Loader;
if (!defined('ABSPATH')) exit;
class YamlFilesLoader extends FilesLoader
{
 public function getFileLoaderInstance($file)
 {
 return new YamlFileLoader($file);
 }
}
