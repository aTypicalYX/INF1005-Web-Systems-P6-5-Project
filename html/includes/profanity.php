<?php
$profanityList = [
    'fuck', 'shit', 'ass', 'bitch', 'bastard', 'cunt',
    'dick', 'pussy', 'cock', 'whore', 'slut', 'nigger',
    'faggot', 'retard', 'crap', 'damn', 'hell',
];

function containsProfanity(string $text, array $wordList): bool {
    $clean = strtolower($text);

    // Normalize leetspeak (e.g. sh1t → shit, a$$ → ass)
    $clean = str_replace(
        ['0', '1', '3', '4', '@', '$', '!'],
        ['o', 'i', 'e', 'a', 'a', 's', 'i'],
        $clean
    );

    // Check 1: Whole-word match on original cleaned text
    // Catches: "fuck", "shit", "ass" etc.
    foreach ($wordList as $word) {
        if (preg_match('/\b' . preg_quote(strtolower($word), '/') . '\b/', $clean)) {
            return true;
        }
    }

    // Check 2: Strip ALL non-alphabetic characters and re-check
    // Catches: "f*ck", "f**k", "f.u.c.k", "f-u-c-k", "f_u_c_k", "f u c k"
    $stripped = preg_replace('/[^a-z]/', '', $clean);
    foreach ($wordList as $word) {
        if (str_contains($stripped, strtolower($word))) {
            return true;
        }
    }

    // Check 3: Remove only symbols (keep spaces) and re-check
    // Catches: "f*ck you", "sh!t" as whole words with spaces preserved
    $noSymbols = preg_replace('/[^a-z\s]/', '', $clean);
    foreach ($wordList as $word) {
        if (preg_match('/\b' . preg_quote(strtolower($word), '/') . '\b/', $noSymbols)) {
            return true;
        }
    }

    return false;
}