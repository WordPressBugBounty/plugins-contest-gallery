<?php
if(!function_exists('cg_invoice_number_logic_result_new')){
    function cg_invoice_number_logic_result_new($InvoiceNumberLogicSelect,$InvoiceNumberLogicOwnPrefix,$InvoiceNumberLogicCustomNumber){

$Year = date('Y');
$Month = date('m');

$InvoiceNumberLogicSelectOptions = <<<HEREDOC
        <option value="unset">Empty (no start with year or month - "Custom number" will be reseted every year)</option>
        <option value="year">Start with YEAR ($Year - "Custom number" will be reseted every year) </option>
        <option value="month">Start with MONTH ($Month - "Custom number" will be reseted every month)</option>
        <option value="year-month">Start with YEAR MONTH ($Year-$Month - "Custom number" will be reseted every month)</option>
        <option value="timestamp">Timestamp only (seconds from 1970 year)</option>
HEREDOC;

        $isTimestamp = false;
        $isOwnPrefix = false;

        $InvoiceNumberLogicResult = '';
	    $selected = '';

	    if(!empty($InvoiceNumberLogicSelect)){
            if($InvoiceNumberLogicSelect=='year'){$InvoiceNumberLogicResult.=date('Y');$InvoiceNumberLogicSelectOptions=str_replace('value="year"','value="year" selected',$InvoiceNumberLogicSelectOptions);
	            $selected = 'year';
            }else if($InvoiceNumberLogicSelect=='month'){$InvoiceNumberLogicResult.=date('m');
                $InvoiceNumberLogicSelectOptions=str_replace('value="month"','value="month" selected',$InvoiceNumberLogicSelectOptions);
	            $selected = 'month';
            }else if($InvoiceNumberLogicSelect=='year-month'){$InvoiceNumberLogicResult.=date('Y-m');
                $InvoiceNumberLogicSelectOptions=str_replace('value="year-month"','value="year-month" selected',$InvoiceNumberLogicSelectOptions);
	            $selected = 'year-month';
            }else if($InvoiceNumberLogicSelect=='timestamp'){$InvoiceNumberLogicResult = time();$isTimestamp=true;
                $InvoiceNumberLogicSelectOptions=str_replace('value="timestamp"','value="timestamp" selected',$InvoiceNumberLogicSelectOptions);
	            $selected = 'timestamp';
            }
        }

        if(!empty($InvoiceNumberLogicOwnPrefix) && !$isTimestamp){
            $isOwnPrefix = true;
            if($InvoiceNumberLogicSelect!='unset'){
                $InvoiceNumberLogicResult .= '-'.$InvoiceNumberLogicOwnPrefix;
            }else{
                $InvoiceNumberLogicResult = $InvoiceNumberLogicOwnPrefix;
            }
        }

        if(!empty($InvoiceNumberLogicCustomNumber) && !$isTimestamp){
            if($isOwnPrefix || $isTimestamp){
                $InvoiceNumberLogicResult .= '-'.$InvoiceNumberLogicCustomNumber;
            }else{
                $InvoiceNumberLogicResult = $InvoiceNumberLogicCustomNumber;
            }
        }

        if(empty($InvoiceNumberLogicCustomNumber)){
            $InvoiceNumberLogicCustomNumber = '0001';
        }

        if(empty($InvoiceNumberLogicResult) && !empty($InvoiceNumberLogicCustomNumber)){
            $InvoiceNumberLogicResult = $InvoiceNumberLogicCustomNumber;
        }else if(empty($InvoiceNumberLogicResult) && empty($InvoiceNumberLogicCustomNumber)){
            $InvoiceNumberLogicResult = '0001';
        }

        $returnArray = [
            'InvoiceNumberLogicResult' => $InvoiceNumberLogicResult,
            'InvoiceNumberLogicSelectOptions' => $InvoiceNumberLogicSelectOptions,
            'InvoiceNumberLogicOwnPrefix' => $InvoiceNumberLogicOwnPrefix,
            'selected' => $selected,
        ];

        return $returnArray;

    }
}
if(!function_exists('cg_paypal_get_invoice_number_logic_result')){
    function cg_paypal_get_invoice_number_logic_result($InvoiceNumberLogic,$isReturnResultArray = false,$ownFinalPart = ''){

        $InvoiceNumberLogicResult = '0001';
        $InvoiceNumberLogicPartOne = '';
        $InvoiceNumberLogicPartTwo = '';
        $InvoiceNumberLogicPartThree = '';
        $InvoiceNumberLogicPartOneResultPrefixDisabled = 'cg_disabled';
        $InvoiceNumberLogicPartTwoResultPrefixDisabled = 'cg_disabled';
        $InvoiceNumberLogicPartThreeResultPrefixDisabled = 'cg_disabled';
        $InvoiceNumberLogicPartOneResultPrefix = '';
        $InvoiceNumberLogicPartTwoResultPrefix = '';
        $InvoiceNumberLogicPartThreeResultPrefix = '';

$InvoiceNumberLogicSelectOptions = <<<HEREDOC
        <option value="unset">Empty</option>
        <option value="year">YEAR (2022)</option>
        <option value="month">MONTH (07)</option>
        <option value="year-month">YEAR MONTH (2022-07)</option>
        <option value="ownprefix">Own prefix</option>
HEREDOC;

        $InvoiceNumberLogicPartOneSelectOptions = $InvoiceNumberLogicSelectOptions;
        $InvoiceNumberLogicPartTwoSelectOptions = $InvoiceNumberLogicSelectOptions;
        $InvoiceNumberLogicPartThreeSelectOptions = $InvoiceNumberLogicSelectOptions;

        $InvoiceNumberLogicResult = '0001';

        $InvoiceNumberLogic = [];

        if(!empty($InvoiceNumberLogic)){
            $InvoiceNumberLogic = unserialize($InvoiceNumberLogic);
            $InvoiceNumberLogicResult = '';

            $InvoiceNumberLogicPartResultPrefix='';
            $InvoiceNumberLogicPartResultPrefixDisabled = 'cg_disabled';
            $InvoiceNumberLogicSelectOptionsSelectedForPart = '';
            if($InvoiceNumberLogicSelect=='unset' OR empty(key($InvoiceNumberLogicPart))){$InvoiceNumberLogicResult.='';
                $InvoiceNumberLogicSelectOptionsSelectedForPart = str_replace('value="unset"','value="unset" selected',$InvoiceNumberLogicSelectOptions);
            }else if($InvoiceNumberLogicSelect=='year'){$InvoiceNumberLogicResult.=date('Y').'-';
                $InvoiceNumberLogicSelectOptionsSelectedForPart = str_replace('value="year"','value="year" selected',$InvoiceNumberLogicSelectOptions);
            }else if($InvoiceNumberLogicSelect=='month'){$InvoiceNumberLogicResult.=date('m').'-';
                $InvoiceNumberLogicSelectOptionsSelectedForPart = str_replace('value="month"','value="month" selected',$InvoiceNumberLogicSelectOptions);}
            if($InvoiceNumberLogicSelect=='year-month'){$InvoiceNumberLogicResult.=date('Y-m').'-';
                $InvoiceNumberLogicSelectOptionsSelectedForPart = str_replace('value="year-month"','value="year-month" selected',$InvoiceNumberLogicSelectOptions);
            }else if($InvoiceNumberLogicSelect=='ownprefix'){$InvoiceNumberLogicResult.=$InvoiceNumberLogicPart[key($InvoiceNumberLogicPart)].'-';$InvoiceNumberLogicPartResultPrefix=$InvoiceNumberLogicPart[key($InvoiceNumberLogicPart)];$InvoiceNumberLogicPartResultPrefixDisabled='';
                $InvoiceNumberLogicSelectOptionsSelectedForPart = str_replace('value="ownprefix"','value="ownprefix" selected',$InvoiceNumberLogicSelectOptions);
            }

            $InvoiceNumberLogicPartOne=$InvoiceNumberLogicSelect;$InvoiceNumberLogicPartOneResultPrefix=$InvoiceNumberLogicPartResultPrefix;$InvoiceNumberLogicPartOneResultPrefixDisabled=$InvoiceNumberLogicPartResultPrefixDisabled;$InvoiceNumberLogicPartOneSelectOptions = $InvoiceNumberLogicSelectOptionsSelectedForPart;


            $InvoiceNumberLogicPartTwo=$InvoiceNumberLogicOwnPrefix;$InvoiceNumberLogicPartTwoResultPrefix=$InvoiceNumberLogicPartResultPrefix;$InvoiceNumberLogicPartTwoResultPrefixDisabled=$InvoiceNumberLogicPartResultPrefixDisabled;$InvoiceNumberLogicPartTwoSelectOptions = $InvoiceNumberLogicSelectOptionsSelectedForPart;

            $InvoiceNumberLogicPartThree=$InvoiceNumberLogicArray['InvoiceNumberFinalPart'];$InvoiceNumberLogicPartThreeResultPrefix=$InvoiceNumberLogicPartResultPrefix;$InvoiceNumberLogicPartThreeResultPrefixDisabled=$InvoiceNumberLogicPartResultPrefixDisabled;$InvoiceNumberLogicPartThreeSelectOptions = $InvoiceNumberLogicSelectOptionsSelectedForPart;

            if(!empty($InvoiceNumberLogicResult)){// then must have - at the end
                if(!empty($ownFinalPart)){
                    $InvoiceNumberLogicResult = $InvoiceNumberLogicResult.$ownFinalPart;
                }else{
                    $InvoiceNumberLogicResult = substr($InvoiceNumberLogicResult,0,strlen($InvoiceNumberLogicResult)-1);
                }
            }else if(empty($InvoiceNumberLogicResult)){
                if(!empty($ownFinalPart)){
                    $InvoiceNumberLogicResult = $ownFinalPart;
                }else{
                    $InvoiceNumberLogicResult = '0001';
                }
            }

        }

        if($isReturnResultArray){
            $returnArray = [
                'InvoiceNumberLogicResult' => $InvoiceNumberLogicResult,
                'InvoiceNumberLogicPartOne' => $InvoiceNumberLogicPartOne,
                'InvoiceNumberLogicPartTwo' => $InvoiceNumberLogicPartTwo,
                'InvoiceNumberLogicPartThree' => $InvoiceNumberLogicPartThree,
                'InvoiceNumberLogicPartOneResultPrefixDisabled' => $InvoiceNumberLogicPartOneResultPrefixDisabled,
                'InvoiceNumberLogicPartTwoResultPrefixDisabled' => $InvoiceNumberLogicPartTwoResultPrefixDisabled,
                'InvoiceNumberLogicPartThreeResultPrefixDisabled' => $InvoiceNumberLogicPartThreeResultPrefixDisabled,
                'InvoiceNumberLogicPartOneResultPrefix' => $InvoiceNumberLogicPartOneResultPrefix,
                'InvoiceNumberLogicPartTwoResultPrefix' => $InvoiceNumberLogicPartTwoResultPrefix,
                'InvoiceNumberLogicPartThreeResultPrefix' => $InvoiceNumberLogicPartThreeResultPrefix,
                'InvoiceNumberLogicPartOneSelectOptions' => $InvoiceNumberLogicPartOneSelectOptions,
                'InvoiceNumberLogicPartTwoSelectOptions' => $InvoiceNumberLogicPartTwoSelectOptions,
                'InvoiceNumberLogicPartThreeSelectOptions' => $InvoiceNumberLogicPartThreeSelectOptions,
            ];

            return $returnArray;

        }else{
            return $InvoiceNumberLogicResult;
        }


    }
}
