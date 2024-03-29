<?php

/* Jison generated parser */

class JisonParser_Phraser
{
    public $symbols_ = [];
    public $terminals_ = [];
    public $productions_ = [];
    public $table = [];
    public $defaultActions = [];
    public $version = '0.3.6';
    public $debug = false;

    public function __construct()
    {
        //ini_set('error_reporting', E_ALL);
        //ini_set('display_errors', 1);

        $accept = 'accept';
        $end = 'end';

        //parser
        $this->symbols_ = json_decode('{"error":2,"html":3,"contents":4,"EOF":5,"content":6,"TAG":7,"WORD":8,"CHAR":9,"$accept":0,"$end":1}', true);
        $this->terminals_ = json_decode('{"2":"error","5":"EOF","7":"TAG","8":"WORD","9":"CHAR"}', true);
        $this->productions_ = json_decode('[0,[3,2],[4,1],[4,2],[6,1],[6,1],[6,1]]', true);
        $this->table = json_decode('[{"3":1,"4":2,"6":3,"7":[1,4],"8":[1,5],"9":[1,6]},{"1":[3]},{"5":[1,7],"6":8,"7":[1,4],"8":[1,5],"9":[1,6]},{"5":[2,2],"7":[2,2],"8":[2,2],"9":[2,2]},{"5":[2,4],"7":[2,4],"8":[2,4],"9":[2,4]},{"5":[2,5],"7":[2,5],"8":[2,5],"9":[2,5]},{"5":[2,6],"7":[2,6],"8":[2,6],"9":[2,6]},{"1":[2,1]},{"5":[2,3],"7":[2,3],"8":[2,3],"9":[2,3]}]', true);
        $this->defaultActions = json_decode('{"7":[2,1]}', true);

        //lexer
        $this->rules = ["/^(?:<(.|\\n)*?>+)/","/^(?:(\\w|\\d)+)/","/^(?:(.|\\n|\\s))/","/^(?:$)/"];
        $this->conditions = json_decode('{"INITIAL":{"rules":[0,1,2,3],"inclusive":true}}', true);

        $this->options = "<@@OPTIONS@@>";
    }

    public function trace()
    {
    }

    public function parser_performAction(&$thisS, $yytext, $yyleng, $yylineno, $yystate, $S, $_S, $O)
    {



        switch ($yystate) {
            case 1:
                return $S[$O - 1];
            break;
            case 2:
                $thisS = $S[$O];
                break;
            case 3:
                $thisS = $S[$O - 1] . $S[$O];

                break;
            case 4:
                    $thisS = $this->tagHandler($S[$O]);

                break;
            case 5:
                    $thisS = $this->wordHandler($S[$O]);

                break;
            case 6:
                    $thisS = $this->charHandler($S[$O]);

                break;
        }
    }

    public function parser_lex()
    {
        $token = $this->lexer_lex(); // $end = 1
        $token = (isset($token) ? $token : 1);

        // if token isn't its numeric value, convert
        if (isset($this->symbols_[$token])) {
            return $this->symbols_[$token];
        }

        return $token;
    }

    public function parseError($str = "", $hash = [])
    {
        throw new Exception($str);
    }

    public function parse($input)
    {
        $stack = [0];
        $stackCount = 1;

        $vstack = [null];
        $vstackCount = 1;
        // semantic value stack

        $lstack = [$this->yyloc];
        $lstackCount = 1;
        //location stack

        $shifts = 0;
        $reductions = 0;
        $recovering = 0;
        $TERROR = 2;

        $this->setInput($input);

        $yyval = (object)[];
        $yyloc = $this->yyloc;
        $lstack[] = $yyloc;

        while (true) {
            // retreive state number from top of stack
            $state = $stack[$stackCount - 1];
            // use default actions if available
            if (isset($this->defaultActions[$state])) {
                $action = $this->defaultActions[$state];
            } else {
                if (empty($symbol) == true) {
                    $symbol = $this->parser_lex();
                }
                // read action for current state and first input
                if (isset($this->table[$state][$symbol])) {
                    $action = $this->table[$state][$symbol];
                } else {
                    $action = '';
                }
            }

            if (empty($action) == true) {
                if (! $recovering) {
                    // Report error
                    $expected = [];
                    foreach ($this->table[$state] as $p => $item) {
                        if (! empty($this->terminals_[$p]) && $p > 2) {
                            $expected[] = $this->terminals_[$p];
                        }
                    }

                    $errStr = "Parse error on line " . ($yylineno + 1) . ":\n" . $this->showPosition() . "\nExpecting " . implode(", ", $expected) . ", got '" . $this->terminals_[$symbol] . "'";

                    $this->parseError($errStr, [
                        "text" => $this->match,
                        "token" => $symbol,
                        "line" => $this->yylineno,
                        "loc" => $yyloc,
                        "expected" => $expected
                    ]);
                }

                // just recovered from another error
                if ($recovering == 3) {
                    if ($symbol == $this->EOF) {
                        $this->parseError(isset($errStr) ? $errStr : 'Parsing halted.');
                    }

                    // discard current lookahead and grab another
                    $yyleng = $this->yyleng;
                    $yytext = $this->yytext;
                    $yylineno = $this->yylineno;
                    $yyloc = $this->yyloc;
                    $symbol = $this->parser_lex();
                }

                // try to recover from error
                while (true) {
                    // check for error recovery rule in this state
                    if (isset($this->table[$state][$TERROR])) {
                        break 2;
                    }
                    if ($state == 0) {
                        $this->parseError(isset($errStr) ? $errStr : 'Parsing halted.');
                    }

                    array_slice($stack, 0, 2);
                    $stackCount -= 2;

                    array_slice($vstack, 0, 1);
                    $vstackCount -= 1;

                    $state = $stack[$stackCount - 1];
                }

                $preErrorSymbol = $symbol; // save the lookahead token
                $symbol = $TERROR; // insert generic error symbol as new lookahead
                $state = $stack[$stackCount - 1];
                if (isset($this->table[$state][$TERROR])) {
                    $action = $this->table[$state][$TERROR];
                }
                $recovering = 3; // allow 3 real symbols to be shifted before reporting a new error
            }

            // this shouldn't happen, unless resolve defaults are off
            if (is_array($action[0])) {
                $this->parseError("Parse Error: multiple actions possible at state: " . $state . ", token: " . $symbol);
            }

            switch ($action[0]) {
                case 1:
                    // shift
                    //$this->shiftCount++;
                    $stack[] = $symbol;
                    $stackCount++;

                    $vstack[] = $this->yytext;
                    $vstackCount++;

                    $lstack[] = $this->yyloc;
                    $lstackCount++;

                    $stack[] = $action[1]; // push state
                    $stackCount++;

                    $symbol = "";
                    if (empty($preErrorSymbol)) { // normal execution/no error
                        $yyleng = $this->yyleng;
                        $yytext = $this->yytext;
                        $yylineno = $this->yylineno;
                        $yyloc = $this->yyloc;
                        if ($recovering > 0) {
                            $recovering--;
                        }
                    } else { // error just occurred, resume old lookahead f/ before error
                        $symbol = $preErrorSymbol;
                        $preErrorSymbol = "";
                    }
                    break;

                case 2:
                    // reduce
                    $len = $this->productions_[$action[1]][1];
                    // perform semantic action
                    $yyval->S = $vstack[$vstackCount - $len];// default to $S = $1
                    // default location, uses first token for firsts, last for lasts
                    $yyval->_S = [
                        "first_line" => $lstack[$lstackCount - (isset($len) ? $len : 1)]['first_line'],
                        "last_line" => $lstack[$lstackCount - 1]['last_line'],
                        "first_column" => $lstack[$lstackCount - (isset($len) ? $len : 1)]['first_column'],
                        "last_column" => $lstack[$lstackCount - 1]['last_column']
                    ];

                    $r = $this->parser_performAction($yyval->S, $yytext, $yyleng, $yylineno, $action[1], $vstack, $lstack, $vstackCount - 1);

                    if (empty($r) == false) {
                        return $r;
                    }

                    // pop off stack
                    if ($len > 0) {
                        $stack = array_slice($stack, 0, -1 * $len * 2);
                        $stackCount -= $len * 2;

                        $vstack = array_slice($vstack, 0, -1 * $len);
                        $vstackCount -= $len;

                        $lstack = array_slice($lstack, 0, -1 * $len);
                        $lstackCount -= $len;
                    }

                    $stack[] = $this->productions_[$action[1]][0]; // push nonterminal (reduce)
                    $stackCount++;

                    $vstack[] = $yyval->S;
                    $vstackCount++;

                    $lstack[] = $yyval->_S;
                    $lstackCount++;

                    // goto new state = table[STATE][NONTERMINAL]
                    $newState = $this->table[$stack[$stackCount - 2]][$stack[$stackCount - 1]];

                    $stack[] = $newState;
                    $stackCount++;

                    break;

                case 3:
                    // accept
                    return true;
            }
        }

        return true;
    }


    /* Jison generated lexer */
    public $EOF = 1;
    public $S = "";
    public $yy = "";
    public $yylineno = 0;
    public $yyleng = 0;
    public $yytext = "";
    public $match = "";
    public $matched = "";
    public $yyloc = [];
    public $conditionsStack = [];
    public $conditionStackCount = 0;
    public $rules = [];
    public $conditions = [];
    public $done = false;
    public $less;
    public $more;
    public $_input;
    public $options;

    public function setInput($input)
    {
        $this->_input = $input;
        $this->more = $this->less = $this->done = false;
        $this->yylineno = $this->yyleng = 0;
        $this->yytext = $this->matched = $this->match = '';
        $this->conditionStack = ['INITIAL'];
        $this->yyloc = [
            "first_line" => 1,
            "first_column" => 0,
            "last_line" => 1,
            "last_column" => 0
        ];
    }

    public function input()
    {
        $ch = $this->_input[0];
        $this->yytext .= $ch;
        $this->yyleng++;
        $this->match .= $ch;
        $this->matched .= $ch;
        $lines = preg_match("/\n/", $ch);
        if (count($lines) > 0) {
            $this->yylineno++;
        }
        array_slice($this->_input, 1);
        return $ch;
    }

    public function unput($ch)
    {
        $this->_input = $ch . $this->_input;
        return $this;
    }

    public function more()
    {
        $this->more = true;
        return $this;
    }

    public function pastInput()
    {
        $past = substr($this->matched, 0, strlen($this->matched) - strlen($this->match));
        return (strlen($past) > 20 ? '...' : '') . preg_replace("/\n/", "", substr($past, -20));
    }

    public function upcomingInput()
    {
        $next = $this->match;
        if (strlen($next) < 20) {
            $next .= substr($this->_input, 0, 20 - strlen($next));
        }
        return preg_replace("/\n/", "", substr($next, 0, 20) . (strlen($next) > 20 ? '...' : ''));
    }

    public function showPosition()
    {
        $pre = $this->pastInput();

        $c = '';
        for ($i = 0, $preLength = strlen($pre); $i < $preLength; $i++) {
            $c .= '-';
        }

        return $pre . $this->upcomingInput() . "\n" . $c . "^";
    }

    public function next()
    {
        if ($this->done == true) {
            return $this->EOF;
        }

        if (empty($this->_input)) {
            $this->done = true;
        }

        if ($this->more == false) {
            $this->yytext = '';
            $this->match = '';
        }

        $rules = $this->currentRules();
        for ($i = 0, $j = count($rules); $i < $j; $i++) {
            preg_match($this->rules[$rules[$i]], $this->_input, $tempMatch);
            if ($tempMatch && (empty($match) || count($tempMatch[0]) > count($match[0]))) {
                $match = $tempMatch;
                $index = $i;
                if (isset($this->options->flex) && $this->options->flex == false) {
                    break;
                }
            }
        }
        if ($match) {
            $matchCount = strlen($match[0]);
            $lineCount = preg_match("/\n.*/", $match[0], $lines);

            if ($lineCount > 1) {
                $this->yylineno += $lineCount;
            }
            $this->yyloc = [
                "first_line" => $this->yyloc['last_line'],
                "last_line" => $this->yylineno + 1,
                "first_column" => $this->yyloc['last_column'],
                "last_column" => $lines ? count($lines[$lineCount - 1]) - 1 : $this->yyloc['last_column'] + $matchCount
            ];
            $this->yytext .= $match[0];
            $this->match .= $match[0];
            $this->yyleng = strlen($this->yytext);
            $this->more = false;
            $this->_input = substr($this->_input, $matchCount, strlen($this->_input));
            $this->matched .= $match[0];
            $token = $this->lexer_performAction($this->yy, $this, $rules[$index], $this->conditionStack[$this->conditionStackCount]);

            if ($this->done == true && empty($this->_input) == false) {
                $this->done = false;
            }

            if (empty($token) == false) {
                return $token;
            } else {
                return;
            }
        }

        if (empty($this->_input)) {
            return $this->EOF;
        } else {
            $this->parseError("Lexical error on line " . ($this->yylineno + 1) . ". Unrecognized text.\n" . $this->showPosition(), [
                "text" => "",
                "token" => null,
                "line" => $this->yylineno
            ]);
        }
    }

    public function lexer_lex()
    {
        $r = $this->next();

        while (empty($r) && $this->done == false) {
            $r = $this->next();
        }

        return $r;
    }

    public function begin($condition)
    {
        $this->conditionStackCount++;
        $this->conditionStack[] = $condition;
    }

    public function popState()
    {
        $this->conditionStackCount--;
        return array_pop($this->conditionStack);
    }

    protected function currentRules()
    {
        return $this->conditions[
            $this->conditionStack[
                $this->conditionStackCount
            ]
        ]['rules'];
    }

    public function lexer_performAction(&$yy, $yy_, $avoiding_name_collisions, $YY_START = null)
    {
        $YYSTATE = $YY_START;



        switch ($avoiding_name_collisions) {
            case 0:
                return 7;
            break;
            case 1:
                return 8;
            break;
            case 2:
                return 9;
            break;
            case 3:
                return 5;
            break;
        }
    }
}
