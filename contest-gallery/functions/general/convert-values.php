<?php

if(!function_exists('contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method')){
    function contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method ($content){
        if(!empty($content)){
            $content = trim($content);
        }else{
            $content = '';
        }

        if(!current_user_can( 'unfiltered_html' ) ){
            $content = wp_kses_post($content);// script tags will be removed
        }

        $content = str_replace("&zwj;", "", $content);// might be inserted by html parser
        $content = htmlentities($content, ENT_QUOTES);
        $content = str_replace("&zwj;", "", $content);// might be inserted by html parser

        //$content = nl2br($content);

        //Ganz wichtig, ansonsten werden bei vielen Servern immer / (Backslashes bei Anf�hrungszeichen und aneren speziellen Sonderzeichen) hinzugef�gt
        $content = preg_replace('/\\\\/', '', $content);

        return cg1l_sanitize_method($content);

    }
}

if(!function_exists('contest_gal1ery_htmlentities_and_preg_replace_new')){
    function contest_gal1ery_htmlentities_and_preg_replace_new ($content){// has to be tested in future for all cases
        if ($content === null) {
            return '';
        }

        // Undo slashes WordPress may add (e.g., from requests)
        $content = wp_unslash($content);
        $content = trim($content);

        // Remove ZWJ noise
        $content = str_replace('&zwj;', '', $content);

        // Remove stray backslashes if you still need that behavior
        $content = preg_replace('/\\\\/', '', $content);

        if (current_user_can('unfiltered_html')) {
            // TRUSTED path: keep scripts and full HTML
            // (No sanitize_textarea_field, no wp_kses_post)
            return $content;
        }

        // UNTRUSTED path: allow basic HTML but no <script>
        // This will remove your GTM script for non-privileged users.
        $content = wp_kses_post($content);
        return $content;
    }
}

if(!function_exists('contest_gal1ery_htmlentities_and_preg_replace')){
    function contest_gal1ery_htmlentities_and_preg_replace ($content){
        if(!empty($content)){
            $content = trim($content);
        }else{
            $content = '';
        }

		if(!current_user_can( 'unfiltered_html' ) ){
			$content = wp_kses_post($content);// script tags will be removed
		}

        $content = str_replace("&zwj;", "", $content);// might be inserted by html parser
        $content = htmlentities($content, ENT_QUOTES);
        $content = str_replace("&zwj;", "", $content);// might be inserted by html parser

        //$content = nl2br($content);

        //Ganz wichtig, ansonsten werden bei vielen Servern immer / (Backslashes bei Anf�hrungszeichen und aneren speziellen Sonderzeichen) hinzugef�gt
        $content = preg_replace('/\\\\/', '', $content);

        return sanitize_textarea_field($content);
    }
}

if(!function_exists('contest_gal1ery_no_convert')){
    function contest_gal1ery_no_convert ($content){
        if(!empty($content)){
            $content = trim($content);
        }else{
            $content = '';
        }
        return $content;
    }
}

if(!function_exists('cg_stripslashes_recursively')){
    function cg_stripslashes_recursively ($content){
        if(!empty($content)){
            $content=implode("",explode("\\",$content));
            return stripslashes(trim($content));
        }else{
            return $content;
        }
    }
}

if(!function_exists('contest_gal1ery_convert_for_html_output_without_nl2br')){
    function contest_gal1ery_convert_for_html_output_without_nl2br ($content){
        if(!empty($content)){
            $content = trim($content);
        }else{
            $content = '';
        }

        $content = str_replace("&zwj;", "", $content);// might be inserted by html parser
        $content = html_entity_decode(cg_stripslashes_recursively($content));

        return $content;
    }
}

if(!function_exists('contest_gal1ery_convert_for_html_header_output')){
    function contest_gal1ery_convert_for_html_header_output ($content){
        if(!empty($content)){
            $content = trim($content);
        }else{
            $content = '';
        }

        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Optional: also handle any extra slashes or newlines
        $content = stripslashes($content);
        $content = trim($content);

        return $content;
    }
}

if(!function_exists('contest_gal1ery_return_bytes')){
    function contest_gal1ery_return_bytes($val) {
        $last = strtolower(substr($val,strlen($val)-1,1));
        $val = intval(trim($val));
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;break;
            case 'm':
                $val *= 1024;break;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
}

if(!function_exists('contest_gal1ery_return_mega_byte')){
    function contest_gal1ery_return_mega_byte($val) {
        $last = strtolower(substr($val,strlen($val)-1,1));
        $val = intval(trim($val));
        switch($last) {
            // The 'G' for Gigabyte
            case 'g':
                $val *= 1000;break;
        }

        return $val;
    }
}

if(!function_exists('cg_get_array_from_multiple_line_textarea')){
    function cg_get_array_from_multiple_line_textarea($text) {

        // Split by any line ending (\r\n, \n, or \r)
        $lines = preg_split('/\r\n|\r|\n/', trim($text));

        // Optionally remove empty lines
        $lines = array_filter($lines);

        // Reset array keys (optional)
        $lines = array_values($lines);

        return $lines;
    }
}

if(!function_exists('cg_neutralize_csv_value')){
    function cg_neutralize_csv_value($value, bool $strict_numbers = true, bool $keep_newlines = true): string
    {
        $v = (string) $value;

        // Steuerzeichen filtern (0x00–0x1F & 0x7F); ggf. CR/LF ausnehmen
        if ($keep_newlines) {
            // Erlaube \r und \n, entferne den Rest
            $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $v);
        } else {
            // Entferne alle Steuerzeichen inkl. CR/LF
            $v = preg_replace('/[\x00-\x1F\x7F]/', '', $v);
        }

        // Formel-/DDE-Injection absichern (= + - @ oder führender Tab)
        if (preg_match('/^\s*([=+\-@]|\t)/', $v)) {
            $trimmed = ltrim($v);
            $needsPrefix = true;

            // ±Zahl zulassen (z. B. -12.3, +5, 1.2e3)
            if ($strict_numbers && preg_match('/^[\+\-]\s*\d*\.?\d+(?:[eE][\+\-]?\d+)?\s*$/', $trimmed)) {
                $needsPrefix = false;
            }

            // '=' oder '@' immer neutralisieren
            if ($trimmed !== '' && ($trimmed[0] === '=' || $trimmed[0] === '@')) {
                $needsPrefix = true;
            }

            // führender Tab immer neutralisieren
            if (isset($v[0]) && $v[0] === "\t") {
                $needsPrefix = true;
            }

            if ($needsPrefix) {
                $v = "'" . $v;
            }
        }

        // Zeilenumbrüche ggf. zu Leerzeichen machen
        if (!$keep_newlines) {
            $v = str_replace(array("\r\n", "\r", "\n"), ' ', $v);
        }

        return $v;
    }
}

if(!function_exists('cg_neutralize_csv_array')){
    function cg_neutralize_csv_array(array $data): array {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // 🔁 Rekursiv tiefer gehen
                $data[$key] = cg_neutralize_csv_array($value);
            } else {
                // 🧼 Einzelwert neutralisieren
                $data[$key] = cg_neutralize_csv_value($value);
            }
        }
        return $data;
    }
}