<?php
// Adds "@internal" as the first tag of the class docblock, before "@implements ComponentProvider".
foreach (array_slice($argv, 1) as $file) {
    $src = file_get_contents($file);
    $new = preg_replace('~^/\*\*\n( \* @implements ComponentProvider<)~m', "/**\n * @internal\n *\n$1", $src, 1, $n);
    if ($n !== 1) {
        fwrite(STDERR, "no match: $file\n");
        continue;
    }
    file_put_contents($file, $new);
}
