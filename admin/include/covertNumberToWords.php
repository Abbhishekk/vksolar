<?php
function convertNumber($number) {
    $number = number_format($number, 2, '.', '');
    list($integer, $fraction) = explode(".", $number);
    
    $output = "";
    if ($integer < 0) {
        $output .= "negative ";
        $integer *= -1;
    }
    
    if (strlen($integer) > 9) {
        return "Number too large";
    }
    
    $words_array = array(
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
        7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
        13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen',
        18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty',
        50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
    );
    
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    
    while ($integer > 0) {
        switch (strlen($integer)) {
            case 9:
                $val = (int)substr($integer, 0, 2);
                if ($val > 0) $output .= ($words_array[$val] ?? '') . " " . $digits[4] . " ";
                $integer = substr($integer, 2);
                break;
            case 8:
                $val = (int)substr($integer, 0, 1);
                if ($val > 0) $output .= ($words_array[$val] ?? '') . " " . $digits[4] . " ";
                $integer = substr($integer, 1);
                break;
            case 7:
                $val = (int)substr($integer, 0, 2);
                if ($val > 0) $output .= ($words_array[$val] ?? '') . " " . $digits[3] . " ";
                $integer = substr($integer, 2);
                break;
            case 6:
                $val = (int)substr($integer, 0, 1);
                if ($val > 0) $output .= ($words_array[$val] ?? '') . " " . $digits[3] . " ";
                $integer = substr($integer, 1);
                break;
            case 5:
                $val = (int)substr($integer, 0, 2);
                if ($val > 0) $output .= ($words_array[$val] ?? '') . " " . $digits[2] . " ";
                $integer = substr($integer, 2);
                break;
            case 4:
                $val = (int)substr($integer, 0, 1);
                if ($val > 0) $output .= ($words_array[$val] ?? '') . " " . $digits[2] . " ";
                $integer = substr($integer, 1);
                break;
            case 3:
                $val = (int)substr($integer, 0, 1);
                if ($val > 0) $output .= ($words_array[$val] ?? '') . " " . $digits[1] . " ";
                $integer = substr($integer, 1);
                break;
            case 2:
                $num = (int)substr($integer, 0, 2);
                if ($num > 20) {
                    $tens = (int)(substr($num, 0, 1)) * 10;
                    $ones = $num % 10;
                    $output .= ($words_array[$tens] ?? '') . " " . ($words_array[$ones] ?? '');
                } else {
                    $output .= ($words_array[$num] ?? '');
                }
                $integer = 0;
                break;
            case 1:
                $output .= ($words_array[(int)$integer] ?? '');
                $integer = 0;
                break;
        }
    }
    
    $output = trim($output);
    if ($output) {
        $output .= " Rupees";
    }
    
    if ($fraction > 0) {
        $fraction = (int)$fraction;
        if ($fraction < 10) {
            $fraction *= 10;
        }
        if ($output) {
            $output .= " and ";
        }
        if ($fraction < 20) {
            $output .= ($words_array[$fraction] ?? '') . " Paise";
        } else {
            $tens = ($fraction - $fraction % 10);
            $ones = $fraction % 10;
            $output .= ($words_array[$tens] ?? '') . " " . ($words_array[$ones] ?? '') . " Paise";
        }
    }
    
    if (!$output) {
        $output = "Zero Rupees";
    }
    
    return $output . " Only";
}
?>