<?php

namespace markhuot\CraftQL\Types;

use yii\base\Component;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Craft;
use craft\elements\Entry;

class Query extends ObjectType {

    function __construct($request) {
        $token = $request->token();

        $config = [
            'name' => 'Query',
            'fields' => [
                'helloWorld' => [
                    'type' => Type::string(),
                    'resolve' => function ($root, $args) {
                      return 'Welcome to GraphQL! You now have a fully functional GraphQL endpoint.';
                    }
                ],
            ],
        ];

        if ($token->can('query:entries') && $token->allowsMatch('/^query:entryType/')) {
            if (!empty($request->entryTypes()->all())) {
                $config['fields']['entries'] = [
                    'type' => Type::listOf(\markhuot\CraftQL\Types\Entry::interface($request)),
                    'description' => 'An array of entries from Craft',
                    'args' => \markhuot\CraftQL\Types\Entry::args($request),
                    'resolve' => $request->entriesCriteria('all', function ($root, $args, $context, $info) {
                        return \craft\elements\Entry::find();
                    }),
                ];

                $config['fields']['entry'] = [
                    'type' => \markhuot\CraftQL\Types\Entry::interface($request),
                    'description' => 'One entry from Craft',
                    'args' => \markhuot\CraftQL\Types\Entry::args($request),
                    'resolve' => $request->entriesCriteria('one', function ($root, $args, $context, $info) {
                        return \craft\elements\Entry::find();
                    }),
                ];
            }
        }

        if ($token->can('query:users')) {
            $config['fields']['users'] = [
                'type' => Type::listOf(\markhuot\CraftQL\Types\User::type($request)),
                'description' => 'Users registered in Craft',
                'args' => \markhuot\CraftQL\Types\User::args(),
                'resolve' => function ($root, $args) {
                    $criteria = \craft\elements\User::find();
                    foreach ($args as $key => $value) {
                        $criteria = $criteria->{$key}($value);
                    }
                    return $criteria->all();
                }
            ];
        }

        if ($token->can('query:sections')) {
            $config['fields']['sections'] = [
                'type' => Type::listOf(\markhuot\CraftQL\Types\Section::type()),
                'description' => 'Sections defined in Craft',
                'args' => [],
                'resolve' => function ($root, $args) {
                    return \Craft::$app->sections->getAllSections();
                }
            ];
        }

        parent::__construct($config);
    }

}