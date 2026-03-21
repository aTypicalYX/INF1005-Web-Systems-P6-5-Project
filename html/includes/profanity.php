<?php

$profanityList = [
    // Long words - safe to substring/stem match
    'fuck', 'shit', 'bitch', 'bastard', 'cunt', 'pussy',
    'whore', 'slut', 'nigger', 'faggot', 'retard',
    // Short words - whole-word match only to avoid false positives
    'ass', 'dick', 'cock', 'crap', 'damn', 'hell',
];

// These short words only match as whole words
// (prevents false positives: "class", "assignment", "cockatoo", "bass", "scrap")
$wholeWordOnly = ['ass', 'dick', 'cock', 'crap', 'damn', 'hell'];


/**
 * Checks if a token with asterisks exactly matches a profanity word.
 * e.g. "f**k"  -> matches "fuck"
 *      "b****" -> matches "bitch"
 */
function matchesWordWithAsterisks(string $input, string $word): bool
{
    $clean = preg_replace('/[^a-z*]/', '', strtolower($input));
    if (!str_contains($clean, '*')) return false;

    // Collapse consecutive asterisks into one wildcard group
    $collapsed = preg_replace('/\*+/', '*', $clean);

    // * = one or more letters
    $pattern = '/^' . str_replace('\*', '[a-z]+', preg_quote($collapsed, '/')) . '$/';
    return (bool) preg_match($pattern, $word);
}


/**
 * Checks if an asterisk token matches a word VARIANT (suffix form).
 * e.g. "f***ing" -> pattern /^f[a-z]+ing$/ -> matches "fucking"
 *      "f*cked"  -> pattern /^f[a-z]+cked$/ -> matches "fucked"
 */
function tokenMatchesProfanityVariant(string $token, string $word): bool
{
    $clean = preg_replace('/[^a-z*]/', '', strtolower($token));
    if (!str_contains($clean, '*')) return false;

    $collapsed = preg_replace('/\*+/', '*', $clean);
    $pattern   = '/^' . str_replace('\*', '[a-z]+', preg_quote($collapsed, '/')) . '$/';

    // Test the base word plus common suffixes
    $variants = [
        $word,
        $word . 's',
        $word . 'ed',
        $word . 'ing',
        $word . 'er',
        $word . 'ers',
        $word . 'ery',
        $word . 'ish',
        $word . 'face',
        $word . 'head',
    ];

    foreach ($variants as $v) {
        if (preg_match($pattern, $v)) return true;
    }

    return false;
}


/**
 * Returns true if $text contains any profanity from $profanityList.
 *
 * Catches:
 *   Plain words:           "fuck", "shit", "bitch"
 *   Leetspeak:             "sh1t", "a$$", "d!ck"
 *   Dotted/dashed/spaced:  "f.u.c.k", "f-u-c-k", "f u c k", "s h i t"
 *   Symbol-masked exact:   "f*ck", "f**k", "f***k", "b****", "b*tch", "c**t"
 *   Symbol-masked variant: "f***ing", "f*cked", "sh*t" (in long sentences)
 *   Suffixed forms:        "fucking", "fucked", "shitting", "bitches"
 *   Buried in sentences:   "what the fuck were you thinking", "you utter f***ing waste"
 *
 * Allows (no false positives):
 *  "assignment", "class", "hello", "cockatoo", "bass guitar", "scrap metal"
 *  "have a great day!", "hope you are doing well :)"
 */
function containsProfanity(string $text, array $wordList, array $wholeWordOnly = []): bool
{
    $clean = strtolower($text);

    // Normalize common leetspeak digit/symbol substitutions
    $clean = str_replace(
        ['0', '1', '3', '4', '@', '$', '!'],
        ['o', 'i', 'e', 'a', 'a', 's', 'i'],
        $clean
    );

    // Check 1: Whole-word match
    // Catches: "fuck", "shit", "what the hell", "sh!t" (after normalization)
    foreach ($wordList as $word) {
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $clean)) {
            return true;
        }
    }

    // Check 1b: Stem/suffix match
    // Catches: "fucking", "fucked", "shitting", "bitches", "bastardly"
    // Skipped for short/whole-word-only words to prevent false positives
    foreach ($wordList as $word) {
        if (in_array($word, $wholeWordOnly)) continue;
        if (preg_match('/\b' . preg_quote($word, '/') . '[a-z]+\b/', $clean)) {
            return true;
        }
    }

    // Check 2: Strip ALL non-alpha, substring match
    // Catches: "f.u.c.k", "f-u-c-k", "f u c k", "s h i t"
    $stripped = preg_replace('/[^a-z]/', '', $clean);
    foreach ($wordList as $word) {
        if (in_array($word, $wholeWordOnly)) continue;
        if (str_contains($stripped, $word)) {
            return true;
        }
    }

    // Check 3: Strip symbols, keep spaces — whole-word match
    // Catches: sh!t -> shit, and restores word boundaries for short words
    $noSymbols = preg_replace('/[^a-z\s]/', '', $clean);
    foreach ($wordList as $word) {
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $noSymbols)) {
            return true;
        }
    }

    // Check 3b: Strip symbols, keep spaces — stem match
    foreach ($wordList as $word) {
        if (in_array($word, $wholeWordOnly)) continue;
        if (preg_match('/\b' . preg_quote($word, '/') . '[a-z]*\b/', $noSymbols)) {
            return true;
        }
    }

    // Check 4: Asterisk pattern matching
    // Catches: "f*ck", "f**k", "f***ing", "f*cked", "b****", "b*tch", "c**t"
    if (str_contains($clean, '*')) {
        $tokens = explode(' ', $clean);

        foreach ($tokens as $token) {
            if (!str_contains($token, '*')) continue;

            foreach ($wordList as $word) {
                // Exact match: f**k = fuck, b**** = bitch
                if (matchesWordWithAsterisks($token, $word)) return true;

                if (!in_array($word, $wholeWordOnly)) {
                    // Variant match: f***ing matches "fucking" pattern
                    if (tokenMatchesProfanityVariant($token, $word)) return true;

                    // Vowel substitution: catches f*ck (u->fuck), b*tch (i→bitch)
                    foreach (['a', 'e', 'i', 'o', 'u'] as $v) {
                        $attempt = preg_replace('/[^a-z]/', '', str_replace('*', $v, $token));
                        if (str_starts_with($attempt, $word)) return true;
                    }
                }
            }
        }

        // Also test the full cleaned string (handles "f * c k" spaced style)
        foreach ($wordList as $word) {
            if (matchesWordWithAsterisks($clean, $word)) return true;
        }
    }

    return false;
}