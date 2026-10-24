<?php
// Inserts " * @internal" before the first tag line of the class docblock that holds "@implements ComponentProvider".
foreach (array_slice($argv, 1) as $file) {
    $src = file_get_contents($file);
    $new = preg_replace_callback(
        '~^/\*\*\n(?:(?! \* @).*\n)*?(?= \* @(?:experimental|implements ComponentProvider<))~m',
        static fn (array $m): string => $m[0] . " * @internal\n",
        $src,
        1,
        $n,
    );
    if ($n !== 1 || !str_contains($new, " * @internal\n")) {
        fwrite(STDERR, "no match: $file\n");
        continue;
    }
    file_put_contents($file, $new);
}
