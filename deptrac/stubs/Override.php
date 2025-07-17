<?php

//stub Override attribute for PHP <8.3

namespace {
    if (!class_exists('Override')) {
        #[\Attribute(\Attribute::TARGET_METHOD)]
        final class Override {}
    }
}