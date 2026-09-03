<?php

class Template
{
    private string $rawHtml;
    private array $flagData;
    private array $prevRenders;
    private bool $modified;

    public function getFlags(): array
    {
        return array_keys($this->flagData);
    }


    /**
     * @throws TemplateException
     */
    public function getOneFlagData(string $flag): array
    {
        if (array_key_exists($flag, $this->flagData)) {
            return $this->flagData[$flag];
        }
        throw new TemplateException("Unknown flag: ".$flag);
    }


    /**
     * @throws TemplateException
     */
    private function __construct(string $template)
    {
        global $cfg;

        if (trim($template) == '') {
            throw new TemplateException("Template cannot be empty");
        }

        $this->rawHtml = $template;
        $this->modified = true;
        $this->prevRenders[] = [];

        if (preg_match_all($cfg['flagTemp'], $template, $flags) !== false) {
            $this->flagData = array();
            foreach ($flags[1] as $flag) {
                $this->flagData[$flag] = array();
            }
        } else {
            throw new TemplateException("Invalid flags: ".$template);
        }
    }


    /**
     * @throws TemplateException
     */
    public function AddData(string $flag, string|Template $data): void
    {
        if (array_key_exists($flag, $this->flagData)) {
            if ($data instanceof Template) {
                $this->flagData[$flag][] = $data; // Keep as Template
            } else {
                $this->flagData[$flag][] = (string) $data;
            }
        } else {
            throw new TemplateException("Unknown flag: ".$flag);
        }
    }


    public function Render($force = false): string
    {

        if ($this->modified || $force) {
            $html = $this->rawHtml;

            foreach ($this->flagData as $flag => $data) {
                $flagContent = '';
                foreach ($data as $value) {
                    if (is_a($value, 'Template')) {
                        $value = $value->Render($force);
                    }
                    $flagContent .= $value;
                }
                $html = str_replace('%!'.$flag.'!%', $flagContent, $html);
            }
        }

        if (count($this->prevRenders) == 3) {
            array_shift($this->prevRenders);
        }
        $this->prevRenders[] = $html;
        $this->modified = false;

        return $this->prevRenders[count($this->prevRenders) - 1];
    }


    /**
     * @throws TemplateException
     */
    public static function Load(string $filename): Template
    {
        global $cfg;

        if (file_exists($cfg['templateFolder'].'/'.$filename) && is_readable($cfg['templateFolder'].'/'.$filename)) {
            $template = file_get_contents($cfg['templateFolder'].'/'.$filename);
            return new Template($template);
        } else {
            throw new TemplateException("Template file does not exist: ".$filename);
        }
    }
}