<?php
class VideoController
{

    /**
     * @var array Vimeo video quality priority
     */
    public $vimeoQualityPrioritet = array('sd', 'hd', 'mobile');

    /**
     * @var string Vimeo video codec priority
     */
    public $vimeoVideoCodec = 'h264';

    /**
     * Get direct URL to Vimeo video file
     *
     * @param string $url to video on Vimeo
     * @return string file URL
     */
    public function getVimeoDirectUrl($url)
    {
        $result = '';
        $videoInfo = $this->getVimeoVideoInfo($url);
        if ($videoInfo && $videoObject = $this->getVimeoQualityVideo($videoInfo->request->files))
        {
            $result = $videoObject->url;
        }
        return $result;
    }

    /**
     * Get Vimeo video info
     *
     * @param string $url to video on Vimeo
     * @return \stdClass|null result
     */
    public function getVimeoVideoInfo($url)
    {
        $videoInfo = null;
        $page = $this->getRemoteContent($url);
        $dom = new \DOMDocument("1.0", "utf-8");
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $page);
        $xPath = new \DOMXpath($dom);
        $video = $xPath->query('//div[@data-config-url]');
        if ($video)
        {
            $videoObj = json_decode($this->getRemoteContent($video->item(0)->getAttribute('data-config-url')));
            if (!property_exists($videoObj, 'message'))
            {
                $videoInfo = $videoObj;
            }
        }
        return $videoInfo;
    }

    /**
     * Get vimeo video object
     *
     * @param stdClass $files object of Vimeo files
     * @return stdClass Video file object
     */
    public function getVimeoQualityVideo($files)
    {
        $video = null;
        if (!property_exists($files, $this->vimeoVideoCodec) && count($files->codecs))
        {
            $this->vimeoVideoCodec = array_shift($files->codecs);
        }
        $codecFiles = $files->{$this->vimeoVideoCodec};
        foreach ($this->vimeoQualityPrioritet as $quality)
        {
            if (property_exists($codecFiles, $quality))
            {
                $video = $codecFiles->{$quality};
                break;
            }
        }
        if (!$video)
        {
            foreach (get_object_vars($codecFiles) as $file)
            {
                $video = $file;
                break;
            }
        }
        return $video;
    }

    /**
     * Get remote content by URL
     *
     * @param string $url remote page URL
     * @return string result content
     */
    public function getRemoteContent($url)
    {
        $output = file_get_contents($url);
        return $output;
    }

}