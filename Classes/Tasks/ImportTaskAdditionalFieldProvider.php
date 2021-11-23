<?php
/*
 * Copyright (c) 2021.
 *
 * @category   Shopware
 *
 * @copyright  2020 Dirk Persky (https://github.com/DirkPersky)
 * @author     Dirk Persky <dirk.persky@gmail.com>
 * @license     AGPL
 */

declare(strict_types=1);

namespace DirkPersky\NewsImport\Tasks;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;

class ImportTaskAdditionalFieldProvider implements AdditionalFieldProviderInterface
{

    /**
     * @param array $taskInfo
     * @param AbstractTask $task
     * @param SchedulerModuleController $parentObject
     * @return array
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $parentObject)
    {
        $additionalFields = [];
        $fields = [
            'format' => ['type' => 'select', 'options' => ['JSON']],
            'path' => ['type' => 'input'],
            'pid' => ['type' => 'input'],
            'mapping' => ['type' => 'textarea'],
            'setSlug' => ['type' => 'checkbox'],
        ];

        $currentAction = $parentObject->getCurrentAction();
        foreach ($fields as $field => $configuration) {
            if (empty($taskInfo[$field])) {
                // set Default Value
                $taskInfo[$field] = '';
                // get prefill value
                if ($currentAction->equals(Action::ADD) && isset($configuration['default'])) {
                    $taskInfo[$field] = $configuration['default'];
                } elseif ($currentAction->equals(Action::EDIT)) {
                    $taskInfo[$field] = $task->$field;
                }
            }

            $value = htmlspecialchars((string)$taskInfo[$field]);
            $html = '';
            switch ($configuration['type']) {
                case 'input':
                    $html = '<input class="form-control" type="text" name="tx_scheduler[' . $field . ']" id="' . $field . '" value="' . $value . '" size="50" />';
                    break;
                case 'checkbox':
                    $checked = $value === '1' ? 'checked' : '';
                    $html = '<input class="checkbox" type="checkbox" name="tx_scheduler[' . $field . ']" id="' . $field . '" value="1" ' . $checked . ' />';
                    break;
                case 'textarea':
                    $html = '<textarea class="form-control" name="tx_scheduler[' . $field . ']" id="' . $field . '">' . $value . '</textarea>';
                    break;
                case 'select':
                    $html = '<select class="form-control" name="tx_scheduler[' . $field . ']" id="' . $field . '">';
                    // loop Options
                    foreach ($configuration['options'] as $item) {
                        $html .= sprintf(
                            '<option %s value="%s">%s</option>',
                            ($taskInfo[$field] === $item) ? 'selected="selected"' : '',
                            $item,
                            $this->translate($field . '.' . $item)
                        );
                    }
                    $html .= '</select>';
                    break;
            }
            $additionalFields[$field] = [
                'code' => $html,
                'label' => $this->translate($field)
            ];
        }
        // return configs
        return $additionalFields;
    }

    /**
     * @param array $data
     * @param SchedulerModuleController $parentObject
     * @return bool
     */
    public function validateAdditionalFields(array &$data, SchedulerModuleController $parentObject)
    {
        $result = true;
        if (empty($data['path'])) {
            $this->addMessage($this->translate('error.noValidPath'), FlashMessage::ERROR);
            $result = false;
        }
        if ((int)($data['pid']) === 0) {
            $this->addMessage($this->translate('error.pid'), FlashMessage::ERROR);
            $result = false;
        }
        return $result;
    }

    /**
     * @param array $submittedData
     * @param AbstractTask $task
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        /** @var ImportTask $task */
        $task->path = $submittedData['path'];
        $task->mapping = $submittedData['mapping'];
        $task->format = $submittedData['format'];
        $task->pid = $submittedData['pid'];
        $task->setSlug = $submittedData['setSlug'];
    }

    /**
     * @param string $key
     * @return string
     */
    protected function translate(string $key): string
    {
        /** @var LanguageService $languageService */
        $languageService = $GLOBALS['LANG'];
        return $languageService->sL('LLL:EXT:dp_news_import/Resources/Private/Language/locallang.xlf:scheduler.' . $key);
    }
}