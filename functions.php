<?php
//Use a curl (Especially useful if get_file_contents disabled on web host)
//to pull all data from a URL
function url_get_contents ($url) {
if (!function_exists('curl_init')){
die('CURL is not installed!'); //Curl will return a false bool and die here if CURL is not enabled.
}
$ch = curl_init(); //Return curl handle
curl_setopt($ch, CURLOPT_URL, $url); //The URL to fetch
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Return curl contents as opposed to dumping
curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate,sdch'); //set encoding
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); //follow redirects
curl_setopt($ch, CURLOPT_VERBOSE, 0);  //I don't know some shit
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:21.0) Gecko/20100101 Firefox/21.0");
$output = curl_exec($ch);

if (FALSE === $output)
throw new Exception(curl_error($ch), curl_errno($ch));

curl_close($ch); //Close handle
return $output; //Return contents
}

function merge_array($r) {
    $merge = array();
    foreach( $r as $word ) {
        //echo $word . "<br/>";
        $merge[$word]++;
    }
    return $merge;
}

function print_array($r, $conn) {
    arsort($r);
    echo "<table><tr></tr><th>Word</th><th>Count</th><th>Type</th></tr>";
    foreach ($r as $word => $count) {
        $results = $conn->query("SELECT word, wordtype FROM entries WHERE word = '" . $word . "' AND wordtype != ''");
        echo $conn->error; // Echo error if necessary.

        $row = mysqli_fetch_object($results);
        $type = $row->wordtype;
        echo "<td><a href='dictionary.php?word=" . $word . "'>" . $word . "</a></td><td> " . $count . "</td><td>" . $type . "</td><tr/>";
    }
    echo "</table>";
}

function show_dom_node(DOMNode $domNode) {
    if(!isset($tag_array)) { $tag_array = array(); }
    foreach ($domNode->childNodes as $node)
    {
        echo "<div>" . htmlspecialchars($node->nodeName.':'.$node->nodeValue) . "</div>";
        $tag_array[$node->nodeName]++;
        if($node->hasChildNodes()) {
            show_dom_node($node);
        }
    }

    if($node === end( $domNode->childNodes ) ) { echo "hello"; }
}

function open_table($r) {
    $s = "<table>";
    foreach($r as $item) {
        $s .= "<th>" . $item . "</th>";
    }
    return $s;
}

function print_link_rows($r) {
    $s = "<tr>
            <td class='link_name'><a href='" . $r[0] . "'>" . implode( "</a>, <a href='" . $r[0] . "'>",$r[1] ) . "</a></td>
            <td>" . count($r[1]) . "</td><td>" . $r[0] . "</td><td>" . $r[2] . "</td>
            <td class='line_graph'><div class='line-graph' style='width: " . ( round($r[2] / $r[3] * 100 ) ) . "%;'>&nbsp;</div></td>
          </tr>";
    return $s;
}

function close_table() {
    return "</table>";
}

function array_sort_by_column(&$arr, $col, $dir = SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}

function print_links($results) {
    $cols = ["Link Text","Txt Count", "Address", "Link Count"];
    $r = Array();
    foreach ($results as $result) {
        ;
        if (!isset($r[$result->getAttribute('href')]['count'])) {
            $r[$result->getAttribute('href')]['count'] = 0;
        }
        $r[$result->getAttribute('href')]['count']++;
        if (in_array($result->textContent, $r[$result->getAttribute('href')]['text'])) {
            continue;
        }
        $r[$result->getAttribute('href')]['text'][] = $result->textContent;
        //echo print_table_rows( [ $result->getAttribute('href'), $result->textContent ] );
    }

    //array_filter($r);
    array_sort_by_column($r, 'count', SORT_DESC);
    $k = key($r);
    $max_count = $r[$k]['count']; // Should probably be driving this with max(), take note if the sort changes

    echo "<h3>links</h3>" . open_table($cols);
    foreach ($r as $k => $v) {
        echo print_link_rows([$k, $v['text'], $v['count'],$max_count]);
    }
    echo close_table();
}
?>