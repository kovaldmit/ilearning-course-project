<?php

namespace App\Service;

use App\Entity\Issue;
use App\Entity\User;
use Exception;
use JiraRestApi\Configuration\ArrayConfiguration;
use JiraRestApi\Issue\Issue as JiraIssue;
use JiraRestApi\Issue\IssueField;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue\IssueType;
use JiraRestApi\JiraException;
use JiraRestApi\Priority\PriorityService;
use JiraRestApi\User\User as JiraUser;
use JiraRestApi\User\UserService;
use JsonMapper_Exception;

class JiraService
{
    private IssueService $issueService;
    private UserService $userService;
    private PriorityService $priorityService;

    private string $jiraBaseUrl;

    /**
     * @throws JiraException
     */
    public function __construct(string $jiraUrl, string $username, string $apiToken)
    {
        $config = new ArrayConfiguration([
            'jiraHost' => $jiraUrl,
            'jiraUser' => $username,
            'jiraPassword' => $apiToken,
            'useV3RestApi' => true,
        ]);

        $this->issueService = new IssueService($config);
        $this->userService = new UserService($config);
        $this->priorityService = new PriorityService($config);

        $this->jiraBaseUrl = $jiraUrl;
    }


    /**
     * @throws Exception
     */
    public function createIssue(
        string $summary,
        string $description,
        string $priority,
        JiraUser $reporter,
        string $url
    ): JiraIssue {
        try {
            $issueField = new IssueField();

            $issueType = new IssueType();
            $issueType->name = 'Support Request';

            $projectKey = 'KAN';

            $issueField->setProjectKey($projectKey)
                ->setSummary($summary)
                ->setDescription($description)
                ->setIssueType($issueType)
                ->setPriorityNameAsString($priority)
                ->setReporterAccountId($reporter->accountId)
                ->addCustomField('customfield_10033', $url);

            return $this->issueService->create($issueField);
        } catch (JiraException | JsonMapper_Exception $e) {
            throw new Exception('Error creating issue: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getUser(User $user): ?JiraUser
    {
        try {
            $response = $this->userService->findUsers([
                'query' => $user->getEmail(),
                'maxResults' => 1,
            ]);
            return $response[0] ?? null;
        } catch (JiraException | JsonMapper_Exception $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw new Exception('Error fetching user: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function createUser(User $user): ?JiraUser
    {
        try {
            return $this->userService->create([
                'emailAddress' => $user->getEmail(),
                'products' => ['jira-software'],
            ]);
        } catch (JiraException | JsonMapper_Exception $e) {
            throw new Exception('Error creation user: ' . $e->getMessage());
        }
    }


    /**
     * @throws Exception
     */
    public function getPriorities(): array
    {
        try {
            return $this->priorityService->getAll();
        } catch (JiraException | JsonMapper_Exception $e) {
            throw new Exception('Error get priority: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getTicketsByUser(User $user): array
    {
        try {
            $jql = 'reporter = "' . $user->getEmail() . '"';
            $issues = $this->issueService->search($jql);

            return array_map(function ($issue) {
                $customIssue = new Issue();
                foreach (get_object_vars($issue) as $property => $value) {
                    $customIssue->$property = $value;
                }
                $customIssue->setWebLink($this->jiraBaseUrl . '/browse/' . $issue->key);
                return $customIssue;
            }, $issues->getIssues());
        } catch (JiraException | JsonMapper_Exception $e) {
            throw new Exception('Error retrieving issues: ' . $e->getMessage());
        }
    }
}
