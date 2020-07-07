
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

namespace Tiki\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Tiki\Lib\Logs\LogsLib;
use TikiLib;

class UserCreateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('users:create')
            ->setDescription('Create a new user')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'User login'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'User email (ignored if login_is_email is enabled)'
            )
            ->addOption(
                'password',
                'p',
                InputOption::VALUE_OPTIONAL,
                'User password'
            )
            ->addOption(
                'groups',
                'G',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'List of supplementary groups of the new account (you can use multiple times)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $prefs;

        /** @var LogsLib $logslib */
        $logslib = TikiLib::lib('logs');

        $login_is_email = ! empty($prefs['login_is_email'])
            && $prefs['login_is_email'] != 'n';

        $login = $input->getArgument('login');

        if ($login_is_email) {
            $email = $login;
        } else {
            $email = $input->getOption('email');
        }
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        if (empty($email)) {
            throw new \Exception("Email is missing or invalid", 1);
        }

        $groups = $input->getOption('groups');
        $password = $input->getOption('password');
        $userlib = TikiLib::lib('user');

        if ($userlib->user_exists($login)) {
            throw new \Exception("User already exists", 1);
        }

        if (empty($password) && ! is_null($password)) {
            $helper = $this->getHelper('question');
            $password = $helper->ask($input, $output, new Question('Password: '));

            if (empty($password)) {
                $output->writeln("Password was let unset");
                $password = null;
            }
        }

        $user = $userlib->add_user($login, $password, $email, null, null, null, null, $groups);

        if (empty($user)) {
            throw new \Exception("Error creating user", 1);
        }

        $user = $userlib->get_user_info($user);
        $output->write(json_encode($user, JSON_PRETTY_PRINT));
        $logslib->add_action('adminusers', 'system', 'system', 'New user created with username ' . $user['login']);
    }
}