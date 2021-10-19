
<?php

// (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id$

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER['SCRIPT_NAME'], basename(__FILE__)) !== false) {
    header('location: index.php');
    exit;
}

/**
 * Class Services_ML_Controller
 */
class Services_ML_Controller
{
    private $mllib;

    const LEARNERS = [
        'Classifiers' => [
            'path' => 'Classifiers',
            'classes' => [
                'AdaBoost',
                'ClassificationTree',
                'ExtraTreeClassifier',
                'GaussianNB',
                'KDNeighbors',
                'KNearestNeighbors',
                'LogitBoost',
                'LogisticRegression',
                'MultilayerPerceptron',
                'NaiveBayes',
                'RadiusNeighbors',
                'RandomForest',
                'SoftmaxClassifier',
                'SVC',
            ]
        ],
        'Regressors' => [
            'path' => 'Regressors',
            'classes' => [
                'Adaline',
                'ExtraTreeRegressor',
                'GradientBoost',
                'KDNeighborsRegressor',
                'KNNRegressor',
                'MLPRegressor',
                'RadiusNeighbors',
                'RegressionTree',
                'Ridge',
                'SVR',
            ]
        ],
        'Clusterers' => [
            'path' => 'Clusterers',
            'classes' => [
                'DBSCAN',
                'FuzzyCMeans',
                'GaussianMixture',
                'KMeans',
                'MeanShift',
            ]
        ],
        'Anomaly Detectors' => [
            'path' => 'AnomalyDetectors',
            'classes' => [
                'GaussianMLE',
                'IsolationForest',
                'LocalOutlierFactor',
                'Loda',
                'OneClassSVM',
                'RobustZScore',
            ]
        ],
        'Transformers: Dimensionality Reduction' => [
            'path' => 'Transformers',
            'classes' => [
                'GaussianRandomProjector',
                'LinearDiscriminantAnalysis',
                'PrincipalComponentAnalysis',
                'SparseRandomProjector',
                'TruncatedSVD',
                'TSNE',
            ]
        ],
        'Transformers: Feature Conversion' => [
            'path' => 'Transformers',
            'classes' => [
                'IntervalDiscretizer',
                'OneHotEncoder',
                'NumericStringConverter',
                'BooleanConverter',
            ]
        ],
        'Transformers: Feature Expansion' => [
            'path' => 'Transformers',
            'classes' => [
                'PolynomialExpander',
            ]
        ],
        'Transformers: Image Transformers' => [
            'path' => 'Transformers',
            'classes' => [
                'ImageResizer',
                'ImageVectorizer',
            ]
        ],
        'Transformers: Imputation' => [
            'path' => 'Transformers',
            'classes' => [
                'HotDeckImputer',
                'KNNImputer',
                'MissingDataImputer',
            ]
        ],
        'Transformers: Natural Language' => [
            'path' => 'Transformers',
            'classes' => [
                'RegexFilter',
                'TextNormalizer',
                'MultibyteTextNormalizer',
                'StopWordFilter',
                'TfIdfTransformer',
                'TokenHashingVectorizer',
                'WordCountVectorizer',
            ]
        ],
        'Transformers: Other' => [
            'path' => 'Transformers',
            'classes' => [
                'LambdaFunction',
            ]
        ],
        'Transformers: Standardization and Normalization' => [
            'path' => 'Transformers',
            'classes' => [
                'L1Normalizer',
                'L2Normalizer',
                'MaxAbsoluteScaler',
                'MinMaxNormalizer',
                'RobustStandardizer',
                'ZScaleStandardizer',
            ]
        ],
    ];

    const TOKENIZERS = [
        'path' => 'Other\Tokenizers',
        'classes' => [
            'KSkipNGram',
            'NGram',
            'Sentence',
            'Whitespace',
            'Word',
            'WordStemmer',
        ]
    ];

    const TREES = [
        'path' => 'Graph\Trees',
        'classes' => [
            'BallTree',
            'KDTree',
        ]
    ];

    const KERNELS = [
        'path' => 'Kernels\Distance',
        'classes' => [
            'Canberra',
            'Cosine',
            'Diagonal',
            'Euclidean',
            'Hamming',
            'Jaccard',
            'Manhattan',
            'Minkowski',
            'SafeEuclidean',
            'SparseCosine',
        ]
    ];

    const NEURALNET_OPTIMIZERS = [
        'path' => 'NeuralNet\Optimizers',
        'classes' => [
            'AdaGrad',
            'AdaMax',
            'Adam',
            'Cyclical',
            'Momentum',
            'RMSProp',
            'StepDecay',
            'Stochastic',
        ]
    ];

    const NEURALNET_COST_FUNCTIONS = [
        'path' => 'NeuralNet\CostFunctions',
        'classes' => [
            'CrossEntropy',
            'HuberLoss',
            'LeastSquares',
            'RelativeEntropy',
        ]
    ];

    const NEURALNET_ACTIVATION_FUNCTIONS = [
        'path' => 'NeuralNet\ActivationFunctions',
        'classes' => [
            'ELU',
            'HyperbolicTangent',
            'LeakyReLU',
            'ReLU',
            'SELU',
            'Sigmoid',
            'SoftPlus',
            'Softmax',
            'Softsign',
            'ThresholdedReLU',
        ]
    ];

    const NEURALNET_INITIALIZERS = [
        'path' => 'NeuralNet\Initializers',
        'classes' => [
            'Constant',
            'He',
            'LeCun',
            'Normal',
            'Uniform',
            'Xavier1',
            'Xavier2',
        ]
    ];

    const NEURALNET_LAYERS = [
        'path' => 'NeuralNet\Layers',
        'classes' => [
            'Activation',
            'BatchNorm',
            'Dense',
            'Dropout',
            'Noise',
            'PReLU',
            'SiLU',
            'Swish',
        ]
    ];

    const METRICS = [
        'path' => 'CrossValidation\Metrics',
        'classes' => [
            'Accuracy',
            'BrierScore',
            'Completeness',
            'FBeta',
            'Homogeneity',
            'Informedness',
            'MCC',
            'MeanAbsoluteError',
            'MeanSquaredError',
            'MedianAbsoluteError',
            'ProbabilisticAccuracy',
            'RandIndex',
            'RMSE',
            'RSquared',
            'SMAPE',
            'TopKAccuracy',
            'VMeasure',
        ]
    ];

    public function setUp()
    {
        $this->mllib = TikiLib::lib('ml');
        Services_Exception_Disabled::check('feature_machine_learning');

        $perms = Perms::get();
        if (! $perms->machine_learning && ! $perms->admin) {
            throw new Services_Exception_Denied();
        }
    }

    public function action_list()
    {
        $models = $this->mllib->get_models();
        return [
            'title' => tr('Machine Learning Models'),
            'models' => $models
        ];
    }

    public function action_create($input)
    {
        Services_Exception_Denied::checkGlobal('tiki_p_admin_machine_learning');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = $this->serializeInput($input);

            if ($template = $input->template->text()) {
                $model['payload'] = $this->mllib->predefined($template);
            }

            $mlmId = $this->mllib->set_model(null, $model);

            $forward = [
                'controller' => 'ml',
                'action' => 'edit',
                'mlmId' => $mlmId
            ];
            return ['FORWARD' => $forward];
        }

        return [
            'title' => tr('Create Machine Learning Model'),
        ];
    }

    public function action_clone($input)
    {
        Services_Exception_Denied::checkGlobal('tiki_p_admin_machine_learning');

        $existing = $this->mllib->get_model($input->mlmId->int());

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = $this->serializeInput($input);
            $model['payload'] = $existing['payload'];

            $mlmId = $this->mllib->set_model(null, $model);

            $forward = [
                'controller' => 'ml',
                'action' => 'edit',
                'mlmId' => $mlmId
            ];
            return ['FORWARD' => $forward];
        }

        return [
            'title' => tr('Clone Machine Learning Model'),
            'existing' => $existing,
        ];
    }

    public function action_edit($input)
    {
        Services_Exception_Denied::checkGlobal('tiki_p_admin_machine_learning');

        $model = $this->getModel($input);
        if ($definition = Tracker_Definition::get($model['sourceTrackerId'])) {
            $fields = $definition->getFields();
        } else {
            $fields = [];
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $mlmId = $this->mllib->set_model($model['mlmId'], $this->serializeInput($input));

            Feedback::success('Model was updated successfully. You might want to train against the source dataset.');

            $forward = [
                'controller' => 'ml',
                'action' => 'list',
            ];
            return ['FORWARD' => $forward];
        }

        return [
            'title' => tr('Edit Machine Learning Model'),
            'model' => $model,
            'fields' => $fields,
            'learners' => self::LEARNERS
        ];
    }

    public function action_model_args($input)
    {
        $class = $input->class->text();
        if (empty($class)) {
            throw new Services_Exception_NotFound(tr('No class chosen.'));
        }

        $args = [];
        try {
            $ref = new ReflectionClass('Rubix\ML\\' . $class);
            $constructor = $ref->getConstructor();
            if ($constructor) {
                foreach ($constructor->getParameters() as $key => $param) {
                    $type = $param->getType();
                    if ($class === 'Classifiers\\MultilayerPerceptron' && $type->getName() == 'array' && $key == 0) {
                        $input_type = 'layers';
                    } elseif ($type->isBuiltin()) {
                        $input_type = 'text';
                    } elseif (strstr($type->getName(), 'Rubix\\ML')) {
                        $input_type = 'rubix';
                    } else {
                        $input_type = $type->getName();
                    }
                    try {
                        $default = $param->getDefaultValue();
                        $required = false;
                    } catch (ReflectionException $e) {
                        $default = null;
                        $required = true;
                    }
                    $args[] = [
                        'name' => $param->getName(),
                        'default' => $default,
                        'required' => $required,
                        'arg_type' => $type->getName(),
                        'input_type' => $input_type,
                    ];
                }
            }
        } catch (ReflectionException $e) {
            throw new Services_Exception_Denied($e->getMessage());
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $arg_values = $input->args->array();
            foreach ($args as $key => $arg) {
                if (isset($arg_values[$arg['name']]) && ! empty($arg_values[$arg['name']])) {
                    if (isset($arg_values[$arg['name']]['classes'])) {
                        $args[$key]['value'] = [];
                        foreach ($arg_values[$arg['name']]['classes'] as $layer_key => $layer_class) {
                            if (! empty($layer_class)) {
                                $hydrated = $this->mllib->hydrate_single($layer_class, json_decode($arg_values[$arg['name']]['args'][$layer_key]));
                                $args[$key]['value'][] = json_decode($hydrated['serialized_args'], true);
                            }
                        }
                    } elseif ($arg['arg_type'] == 'array') {
                        $args[$key]['value'] = explode(',', $arg_values[$arg['name']]);
                    } elseif ($arg['input_type'] == 'rubix') {
                        $hydrated = $this->mllib->hydrate_single($arg_values[$arg['name']]['class'], json_decode($arg_values[$arg['name']]['args']));
                        $args[$key]['value'] = json_decode($hydrated['serialized_args'], true);
                    } elseif ($arg['arg_type'] == 'bool') {
                        if ($arg_values[$arg['name']] === 'false') {
                            $args[$key]['value'] = false;
                        } else {
                            $args[$key]['value'] = (bool)$arg_values[$arg['name']];
                        }
                    } else {
                        $args[$key]['value'] = $arg_values[$arg['name']];
                    }
                } else {
                    $args[$key]['value'] = $arg['default'];
                }
            }
            $payload = [[
                'class' => $class,
                'args' => $args,
            ]];
            $instances = $this->mllib->hydrate(json_encode($payload));
            return [
                'learner' => preg_replace('/^[^\\\\]*\\\\/', '', $instances[0]['class']),
                'arguments' => method_exists($instances[0]['instance'], '__toString') ? (string)$instances[0]['instance'] : get_class($instances[0]['instance']),
                'payload' => $payload[0],
            ];
        }

        return [
            'title' => tr('%0 arguments', preg_replace('/^[^\\\\]*\\\\/', '', $class)),
            'class' => $class,
            'args' => $args,
            'tokenizers' => self::TOKENIZERS,
            'trees' => self::TREES,
            'kernels' => self::KERNELS,
            'neuralnet_optimizers' => self::NEURALNET_OPTIMIZERS,
            'neuralnet_cost_functions' => self::NEURALNET_COST_FUNCTIONS,
            'neuralnet_activation_functions' => self::NEURALNET_ACTIVATION_FUNCTIONS,
            'neuralnet_initializers' => self::NEURALNET_INITIALIZERS,
            'neuralnet_layers' => self::NEURALNET_LAYERS,
            'learners' => self::LEARNERS,
            'metrics' => self::METRICS,
        ];
    }

    public function action_delete($input)
    {
        $mlmId = $input->mlmId->int();

        Services_Exception_Denied::checkGlobal('tiki_p_admin_machine_learning');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->mllib->delete_model($mlmId);
        }

        return [
            'title' => tr('Remove Model'),
            'mlmId' => $mlmId,
        ];
    }

    public function action_test($input)
    {
        Services_Exception_Denied::checkGlobal('tiki_p_admin_machine_learning');

        $model = $this->getModel($input);

        try {
            $this->mllib->train($model, true);
            Feedback::success(tr('Successfully trained a sample of the data using the model.'));
        } catch (Exception | TypeError $e) {
            Feedback::error(tr('Error while trying to train the model: %0', $e->getMessage()));
        }

        $forward = [
            'controller' => 'ml',
            'action' => 'list',
        ];
        return ['FORWARD' => $forward];
    }

    public function action_train($input)
    {
        Services_Exception_Denied::checkGlobal('tiki_p_admin_machine_learning');

        $model = $this->getModel($input);

        try {
            $this->mllib->train($model, false);
            Feedback::success(tr('Successfully trained the model.'));
        } catch (Exception | TypeError $e) {
            Feedback::error(tr('Error while trying to train the model: %0', $e->getMessage()));
        }

        $forward = [
            'controller' => 'ml',
            'action' => 'list',
        ];
        return ['FORWARD' => $forward];
    }

    public function action_use($input)
    {
        Services_Exception_Denied::checkGlobal('tiki_p_machine_learning');

        $model = $this->getModel($input);
        try {
            $this->mllib->ensureModelTrained($model);
        } catch (Exception $e) {
            Feedback::error($e->getMessage());
            $forward = [
                'controller' => 'ml',
                'action' => 'list',
            ];
            return ['FORWARD' => $forward];
        }

        $itemObject = Tracker_Item::newItem($model['sourceTrackerId']);

        $processedFields = $itemObject->prepareInput($input);
        foreach ($processedFields as $key => $field) {
            if (! in_array($field['fieldId'], $model['trackerFields'])) {
                unset($processedFields[$key]);
            }
        }

        $label = null;
        if (is_int($model['labelField'])) {
            foreach ($processedFields as $field) {
                if ($field['fieldId'] == $model['labelField']) {
                    $label = $field['name'];
                }
            }
        }

        $results = [];
        $result = null;

        if (! empty($processedFields) && $_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $type = $input->type->text();
                if ($type == 'predict') {
                    $prediction = $this->mllib->predictSample($model, $processedFields);
                    if ($model['labelField'] == 'itemId') {
                        $results[$prediction] = ['fields' => []];
                    } else {
                        $result = $prediction;
                    }
                } else {
                    $results = $this->mllib->probaSample($model, $processedFields);
                    if (empty($results)) {
                        throw new Exception("Failed to get probability. Not supported by model.");
                    }
                    foreach ($results as $itemId => $proba) {
                        $results[$itemId] = ['proba' => number_format($proba * 100, 2), 'fields' => []];
                    }
                }
                foreach ($results as $itemId => $_) {
                    $item = Tracker_Item::fromId($itemId);
                    $outputFields = $item->prepareOutput();
                    foreach ($processedFields as $field) {
                        foreach ($outputFields as $outputField) {
                            if ($field['fieldId'] == $outputField['fieldId']) {
                                $results[$itemId]['fields'][] = $outputField;
                            }
                        }
                    }
                }
                Feedback::success("Success");
            } catch (Exception $e) {
                Feedback::error($e->getMessage());
                $forward = [
                    'controller' => 'ml',
                    'action' => 'list',
                ];
                return ['FORWARD' => $forward];
            }
        }

        return [
            'title' => tr('Use machine learning model %0', $model['name']),
            'model' => $model,
            'trackerId' => $model['sourceTrackerId'],
            'fields' => $processedFields,
            'results' => $results,
            'result' => $result,
            'label' => $label,
            'type' => $input->type->text(),
        ];
    }

    protected function serializeInput($input)
    {
        $trackerId = $input->trackerId->int();
        $definition = Tracker_Definition::get($trackerId);

        if (! $definition) {
            throw new Services_Exception_NotFound(tr('Tracker %0 not found', $trackerId));
        }

        return [
            'name' => $input->name->text(),
            'description' => $input->description->text(),
            'sourceTrackerId' => $trackerId,
            'trackerFields' => $input->fields->array(),
            'labelField' => $input->labelField->text(),
            'ignoreEmpty' => $input->ignoreEmpty->int(),
            'payload' => $input->payload->text(),
        ];
    }

    protected function getModel($input)
    {
        $model = $this->mllib->get_model($input->mlmId->int());
        if (! $model) {
            throw new Services_Exception_NotFound(tr('Model not found.'));
        }
        return $model;
    }
}