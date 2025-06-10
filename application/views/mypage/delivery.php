<style>
    .mainWrap {
        padding: 0 10px;
        padding-bottom: 50px;
    }
    
    span {
        color: black !important;
    }
    caption {
        text-align: center;
    }
    /* table */
    table.table_col {
        width: 100%;
        padding: 0px;
        margin: 0px 0px 20px 0px;
        border-top: 2px solid #888;
        border-right: 1px solid #e0e0e0;
    }

    table.table_col thead tr {
        background-color: #fbfbfb;
        margin: 0px;
        border-bottom: 1px solid #e0e0e0;
    }

    table.table_col thead tr th {
        background-image: url(../../np2assets/images/inc/table_col_th_line.gif);
        background-position: right top;
        background-repeat: no-repeat;
        font-size: 12px;
        text-align: center;
        color: #656565;
        line-height: 22px;
        padding: 5px 0px 5px 0px;
        margin: 0px;
        border-bottom: 1px solid #e0e0e0;
    }

    table.table_col thead tr th:first-child {
        border-left: 1px solid #e0e0e0;
        padding: 5px 0px;
    }

    table.table_col tbody tr th, table.table_col tbody tr td {
        min-height: 22px;
        font-size: 12px;
        text-align: center;
        font-weight: normal;
        color: #454545;
        line-height: 22px;
        padding: 5px 10px;
        margin: 0px;
        border-bottom: 1px solid #e0e0e0;
        border-left: 1px solid #e0e0e0;
        background: #fff;
    }

    table.table_col tbody tr td a {
        color: #454545;
    }

    table.table_col tbody tr td a.btn_s {
        line-height: 22px;
    }

    table.table_col tbody tr td a.btn_s.gray {
        color: #444;
        line-height: 24px;
    }

    table.table_col tbody tr td ul.ul_list > li {
        font-size: 12px;
    }

    table.table_col tbody tr td ul.ul_list > li > li {
        font-size: 12px;
    }

    table.table_col tfoot tr th {
        background-color: #fbfbfb;
        font-size: 14px;
        border-bottom: 1px solid #c5c5c5;
    }

    table.table_col tfoot tr td {
        background-color: #fbfbfb;
        text-align: center;
        color: #ff381d;
        font-size: 12px;
        font-weight: bold;
        padding: 11px 12px;
        border-bottom: 1px solid #c5c5c5;
    }

    table.table_col tfoot.v2 tr th {
        background-color: #fbfbfb;
        font-size: 14px;
        border-bottom: 1px solid #c5c5c5;
        color: #656565;
    }

    table.table_col tfoot.v2 tr td {
        background-color: #fbfbfb;
        text-align: center;
        font-size: 13px;
        color: #656565;
        font-weight: normal;
        text-align: left;
        line-height: 18px;
        padding: 11px 12px;
        border-bottom: 1px solid #c5c5c5;
        border-left: 1px solid #e0e0e0;
    }

    table.table_col tfoot.v3 tr th {
        border-left: 1px solid #e0e0e0;
        border-right: 1px solid #e0e0e0;
    }

    table.table_col tfoot.v3 tr td {
        background-color: #fff;
        color: #454545;
        font-weight: normal
    }

    table.table_col tfoot.v3 tr td span {
        font-weight: bold;
    }

    table.table_col.v2 tbody tr th {
        background-color: #fbfbfb;
        font-weight: bold;
        color: #656565;
    }

    table.table_col.v2 tbody tr td {
        text-align: left;
    }

    table.table_col.tb_center td {
        text-align: center !important;
    }

    table.table_col.v2.mod tbody tr td {
        text-align: center;
    }

    table.table_col.v3 {
        width: 40%;
        float: left;
        margin-right: 1%;
    }

    table.table_col.v3 tbody tr td {
        text-align: left;
        vertical-align: top;
    }

    table.table_col.v4 {
        width: 81%;
        float: left;
        margin-right: 1%;
    }

    table.table_col.v4 tbody tr td {
        text-align: left;
        vertical-align: top;
    }

    table.table_col.v5 tbody tr th {
        background-color: #fcfcfc;
        font-weight: bold;
    }

    table.table_col.v6 tbody tr th {
        background-color: #fcfcfc;
        font-weight: bold;
        text-align: left;
        padding-left: 30px;
    }

    table.table_col.v6 tbody tr td {
        text-align: left;
        padding: 10px 20px;
    }

    table.table_col.v7 tbody tr th {
        background-color: #fff;
        font-weight: normal;
        font-size: 11px;
        text-align: center;
        padding: 5px 2px;
    }

    table.table_col.v7 tbody tr td {
        text-align: center;
        font-size: 11px;
        padding: 5px 1px;
        line-height: 18px;
    }

    table.table_col.v7 tbody tr:nth-child(odd) {
        background-color: #f5f8f9;
    }

    table.table_col.v7 tbody tr:nth-child(even) {
        background-color: #FFF;
    }

    table.table_col.v7 tbody tr:hover {
        background: #ddecff
    }

    table.table_col.v8 tbody tr:nth-child(odd) {
        background-color: #f5f8f9 !important;
    }

    table.table_col.v8 tbody tr:nth-child(even) {
        background-color: #FFF !important;
    }

    table.table_col.v8 tbody tr th, table.table_col.v8 tbody tr td {
        padding: 3px 5px;
    }
    
    #ft_menu {
        display: none;
    }
</style>
<?php
    $url = "https://service.epost.go.kr/trace.RetrieveDomRigiTraceList.comm?sid1={$registrationNumber}&displayHeader=";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($ch);
    curl_close($ch);

    if ($html === false) {
        die('Error fetching the URL.');
    }

    // Create a new DOMDocument instance
    $dom = new DOMDocument();

    // Suppress errors due to malformed HTML
    libxml_use_internal_errors(true);

    // Load the HTML into the DOMDocument instance
    $dom->loadHTML($html);

    // Restore error handling
    libxml_clear_errors();

    // Create a new DOMXPath instance
    $xpath = new DOMXPath($dom);

    // Query the DOM for elements with the id 'processTable'
    $elements = $xpath->query('//table[@id="processTable"]');

    // Check if any elements are found
    if ($elements->length > 0) {
        // Use the first matched element
        $element = $elements->item(0);

        // Find and remove the specific text node
        foreach ($element->childNodes as $child) {
            removeTextNode($child, '(집배원 정보 보기)');
        }

        // Output the HTML content of the element
        echo $dom->saveHTML($element);
    } else {
        echo 'No elements found.';
    }

    // Function to remove specific text node
    function removeTextNode($node, $textToRemove) {
        if ($node->nodeType == XML_TEXT_NODE && strpos($node->nodeValue, $textToRemove) !== false) {
            $node->nodeValue = str_replace($textToRemove, '', $node->nodeValue);
        }
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                removeTextNode($child, $textToRemove);
            }
        }
    }
?>