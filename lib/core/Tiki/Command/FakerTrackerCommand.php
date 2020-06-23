
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Faker\Factory as FakerFactory;
use TikiLib;
use Tiki\Faker as TikiFaker;
use Tracker_Definition;

/**
 * Enabled the usage of Faker as a way to load random data to trackers
 */
class FakerTrackerCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('faker:tracker')
            ->setDescription('Generate tracker fake data')
            ->addArgument(
                'tracker',
                InputArgument::REQUIRED,
                'Tracker id'
            )
            ->addOption(
                'field',
                'f',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Override default faker for field. Format: field,faker[,faker_options]. Example: 1,text,30'
            )
            ->addOption(
                'items',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Number of items to generate',
                100
            )
            ->addOption(
                'random-status',
                'r',
                InputOption::VALUE_NONE,
                'Generate random item status'
            )
            ->addOption(
                'reuse-files',
                null,
                InputOption::VALUE_OPTIONAL,
                'Reuse existing files in the file gallery when possible',
                1
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if (! class_exists('\Faker\Factory')) {
            $output->writeln('<error>' . tra('Please install Faker package') . '</error>');
            return;
        }

        $trackerId = $input->getArgument('tracker');
        $numberItems = $input->getOption('items');
        $randomizeStatus = empty($input->getOption('random-status')) ? false : true;
        $fieldOverrideDefinition = $input->getOption('field');
        $reuseFiles = empty($input->getOption('reuse-files')) ? false : true;

        if (! is_numeric($numberItems)) {
            $output->writeln('<error>' . tra('The value of items is not a number') . '</error>');
            return;
        }

        $trackerDefinition = Tracker_Definition::get($trackerId);
        if (! $trackerDefinition) {
            $output->writeln('<error>' . tr('Tracker not found') . '</error>');
            return;
        }

        $fieldFakerOverride = [];
        foreach ($fieldOverrideDefinition as $fieldDefinition) {
            $arguments = array_map('trim', explode(',', $fieldDefinition));
            $fieldReference = array_shift($arguments);
            $action = array_shift($arguments);

            if (is_null($fieldReference) || is_null($action)) {
                $output->writeln('<error>' . tr('Invalid field definition: %0', $fieldDefinition) . '</error>');
                return;
            }

            if (empty($arguments)) {
                $fieldFakerOverride[$fieldReference] = $action;
            } else {
                $fieldFakerOverride[$fieldReference] = [$action, $arguments];
            }
        }

        $trackerFields = $trackerDefinition->getFields();
        $fakerFieldTypes = $this->mapTrackerItems();

        $fieldFakerMap = [];
        foreach ($trackerFields as $field) {
            if (isset($fieldFakerOverride[$field['fieldId']])) { // override by fieldId
                $fakerForField = $fieldFakerOverride[$field['fieldId']];
            } elseif (isset($fieldFakerOverride[$field['permName']])) { // override by permName
                $fakerForField = $fieldFakerOverride[$field['permName']];
            } elseif (preg_match('/~tc~faker:(.*)~\/tc~/', $field['description'], $matches)) {
                // check field descriptions for a faker formatter in the form of
                // ~tc~faker:lastName~/tc~ or ~tc~faker: numberBetween,1,100~/tc~ or ~tc~faker:dateTimeBetween,-6 months~/tc~
                // full list is here https://github.com/fzaninotto/Faker#formatters
                $parts = explode(',', trim($matches[1]));
                $formatter = array_shift($parts);
                if (! empty($parts)) {
                    $formatter = [$formatter, $parts];
                }
                $fakerForField = $formatter;
            } elseif (isset($fakerFieldTypes[$field['type']])) { // default for field type
                $fakerForField = $fakerFieldTypes[$field['type']];
            } else { // if not defined, empty
                $fakerForField = '';
            }

            $fieldFakerMap[] = [
                'fieldId' => $field['fieldId'],
                'faker' => $fakerForField,
            ];
        }

        /** @var \TrackerLib $trackerLib */
        $trackerLib = TikiLib::lib('trk');
        $faker = FakerFactory::create();
        $tikiFaker = new TikiFaker($faker);
        $tikiFaker->setTikiFilesReuseFiles($reuseFiles);
        $faker->addProvider($tikiFaker);

        for ($i = 0; $i < $numberItems; $i++) {
            $fieldData = [];

            foreach ($fieldFakerMap as $fieldFaker) {
                $value = '';

                if (is_array($fieldFaker['faker'])) {
                    $fakerAction = $fieldFaker['faker'][0];
                    $fakerArguments = $fieldFaker['faker'][1];
                    if (! is_array($fakerArguments)) {
                        if ($fakerArguments == 'fieldId') {
                            $fakerArguments = $trackerDefinition->getField($fieldFaker['fieldId']);
                        }
                        $fakerArguments = [$fakerArguments];
                    } elseif ($fakerArguments[0] === 'fieldId') {
                        $fakerArguments[0] = $trackerDefinition->getField($fieldFaker['fieldId']);
                    } elseif (substr($fakerAction, 0, 4) === 'tiki') {
                        array_unshift($fakerArguments, $trackerDefinition->getField($fieldFaker['fieldId']));
                    }
                    $value = call_user_func_array([$faker, $fakerAction], $fakerArguments);
                } elseif (! empty($fieldFaker['faker'])) {
                    $fakerAction = $fieldFaker['faker'];
                    $value = $faker->$fakerAction;
                }

                if (isset($value)) {
                    if (is_object($value) && get_class($value) === 'DateTime') {
                        $value = $value->format('U');
                    }
                    $fieldData[] = array_merge(
                        $trackerLib->get_field_info($fieldFaker['fieldId']),
                        [
                            'value' => $value,
                        ]
                    );
                }
            }

            if (! empty($fieldData)) {
                $status = ($randomizeStatus) ? array_rand(\TikiLib::lib('trk')->status_types()) : '';
                $trackerLib->replace_item($trackerId, 0, ['data' => $fieldData], $status);
            }
        }
    }

    /**
     * Return the current map of tracker field types to faker formatters
     *
     * @return array
     */
    protected function mapTrackerItems()
    {
        $map = [
            'e' => 'tikiCategories', // Category - lookup on the valid values for category
            'c' => 'tikiCheckbox', // Checkbox - lookup on the valid values for the checkbox
            'y' => 'country', // Country Selector (improvement if uses the list from Tiki directly)
            'b' => ['numberBetween', [0, 10000]], // Currency Field
            'f' => 'unixTime', // Date and Time
            'j' => 'unixTime', // Date and Time
            'd' => ['tikiDropdown', 'fieldId'], // Drop Down - lookup on the valid values for the drop down
            'D' => ['tikiDropdown', 'fieldId'], // Drop Down with Other field - lookup on the valid values for the drop down
            'R' => ['tikiRadio', 'fieldId'], // Radio Buttons - lookup for valid values for Buttons
            'M' => ['tikiMultiselect', 'fieldId'], // Multiselect - lookup on the valid values for the select
            'w' => '', // Dynamic Items List - lookup on the valid values from other tracker (and sync between different fields)
            'm' => 'email', // Email
            'FG' => ['tikiFiles', 'fieldId'], // Files - lookup on valid files from gallery
            'h' => '', // Header - empty
            'icon' => ['tikiFiles', ['fieldId', true]], // Icon - lookup on valid files from gallery
            'r' => ['tikiItemLink', 'fieldId'], // Item Link - lookup on valid items
            //'l' => '', // Items List - lookup on valid items
            'LANG' => 'languageCode', // Language
            'G' => 'tikiLocation', // Location - needs geo coordinates in the format <lat>,<long>,<zoom>
            'math' => '', // Mathematical Calculation - empty
            'n' => ['numberBetween', [0, 10000]], // Numeric Field
            'k' => 'tikiPageSelector', // Page Selector - lookup for valid page names
            'S' => ['tikiStaticText', 'fieldId'], // Static Text - empty
            'a' => 'text', // Text Area
            't' => ['text', [30]], // Text Field
            'q' => ['tikiUniqueIdentifier', 'fieldId'], // AutoIncrement - empty
            'L' => 'url', // Url
            'u' => ['tikiUserSelector', 'fieldId'], // User Selector - lookup for valid users
            'g' => 'tikiGroupSelector', // Group Selector - lookup for valid groups
            'wiki' => 'text', //Wiki Page
            //'x' => '', // Action - Not supported
            'articles' => 'tikiArticles', // Articles - lookup for valid articles
            //'C' => '', // Computed Field - not supported - backward compatibility
            //'A' => '', // Attachment - deprecated in favor of files field
            'F' => ['words', [3, true]], // Tags
            //'GF' => '', // Geographic Feature
            //'i' => '', // Image - deprecated in favor of the files field
            //'N' => '', // In Group
            'I' => 'localIpv4', // IP Selector
            //'kaltura' => '', // Kaltura video
            'p' => '', // Ldap lookup - empty
            'STARS' => ['tikiRating', 'fieldId'], // Rating - lookup for valid values
            //'*' => '', // Stars (deprecated)
            //'s' => '', // Stars (system - deprecated)
            'REL' => ['tikiRelations', ['fieldId', true, 5]], // Relations, rand (true/false), max/number of relations
            //'STO' => '', // show.tiki.org - Not supported
            'usergroups' => '', // Display list of user groups - empty
            //'p' => '', // User Preference - Not Supported
            //'U' => '', // User Subscription
            'W' => '', // Webservice - empty
        ];

        return $map;
    }
}