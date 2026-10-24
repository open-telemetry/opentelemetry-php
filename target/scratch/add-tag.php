<?php
// Usage: add-tag.php <tag> [--final] files...
// Adds " * @<tag>" to the class docblock, creating one if missing. --final also makes a plain "class" final.
$tag = $argv[1];
$final = $argv[2] === '--final';
foreach (array_slice($argv, $final ? 3 : 2) as $file) {
    $src = file_get_contents($file);
    $decl = '~^((?:final |abstract |readonly )*(?:class|interface|trait|enum) )~m';
    if (!preg_match($decl, $src, $m, PREG_OFFSET_CAPTURE)) {
        fwrite(STDERR, "no declaration: $file\n");
        continue;
    }
    $pos = $m[0][1];
    $before = substr($src, 0, $pos);
    if (preg_match('~/\*\*\n((?: \*.*\n)*?) \*/\n(?:#\[.*\]\n)*$~', $before, $d, PREG_OFFSET_CAPTURE)) {
        $body = $d[1][0];
        $insert = $body === '' ? " * @$tag\n" : $body . " *\n * @$tag\n";
        if (preg_match('~^ \* @~m', $body)) {
            $insert = $body . " * @$tag\n";
        }
        $before = substr_replace($before, $insert, $d[1][1], strlen($body));
    } else {
        $before .= "/**\n * @$tag\n */\n";
    }
    $rest = substr($src, $pos);
    if ($final && str_starts_with($rest, 'class ')) {
        $rest = 'final ' . $rest;
    }
    file_put_contents($file, $before . $rest);
}
