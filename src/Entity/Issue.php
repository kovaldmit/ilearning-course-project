<?php

namespace App\Entity;

use JiraRestApi\Issue\Issue as JiraIssue;

class Issue extends JiraIssue
{
    private string $webLink;

    public function getWebLink(): string
    {
        return $this->webLink;
    }

    public function setWebLink(string $webLink): self
    {
        $this->webLink = $webLink;
        return $this;
    }
}
