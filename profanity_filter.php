<?php 
function contains_profanity($text): bool{
    $profanities = [
        // Mild / disruptive
        'crap', 'damn', 'heck', 'butt', 'fart', 'screw', 'poop', 'pee', 'dookie', 'booty', 'wiener', 'butthead',

        // Explicit
        'ass', 'shit', 'fuck', 'bitch', 'bastard', 'dick', 'pussy', 'cock', 'cunt', 'slut', 'twat', 'tit', 'boob',

        // Sexual
        '69', 'sex', 'nude', 'naked', 'boobs', 'penis', 'vagina', 'hump', 'suck', 'jerk', 'bang', 'moan',
        'cum', 'cumming', 'nipple', 'nipslip', 'boobjob', 'dildo', 'strapon', 'cumshot', 'jizz', 'handjob',
        'blowjob', 'bj', 'porn', 'xxx', 'camgirl', 'onlyfans', 'threesome', 'hardcore',
        'softcore', 'erotic', 'wet dream', 'playboy', 'stripper', 'peen',

        // Racial & hate speech
        'nigger', 'chink', 'spic', 'gook', 'retard', 'fag', 'faggot', 'dyke', 'tranny', 'kike', 'coon',
        'wetback', 'terrorist', 'muslim', 'nazis', 'nazi', 'islamist', 'slanty', 'sandnigger','nigga',

        // Violent/self-harm
        'kill', 'suicide', 'murder', 'bomb', 'stab', 'shoot', 'rape', 'abuse', 'decapitate',
        'explode', 'hanging', 'blood', 'bloodbath', 'gore', 'massacre', 'unalive', 'selfharm',

        // Bullying terms
        'stupid', 'moron', 'dumb', 'loser', 'ugly', 'fat', 'whore', 'hoe', 'retard', 'pig',
        'idiot', 'bimbo', 'weirdo', 'freak', 'nerd', 'crybaby', 'jerkface', 'booger', 'poophead',

        // Predatory / grooming
        'groomer', 'groom', 'predator', 'manipulate', 'abduct', 'stalk', 'blackmail', 'pedo', 'pedophile', 'creep', 'up late', 'wyd',

        // Substances
        'lsd', 'acid', 'xanax', 'adderall', 'vape', 'nicotine', 'smoke', 'beer', 'liquor', 'drunk', 'shotgun beer',

        // Platforms
        'onlyfans', 'pornhub', 'xvideos', 'redtube', 'spankbang', 'cam4', 'myfreecams', 'livejasmin',
        'nsfw', 'snapchat nudes', 'discord groomer', 'tinder', 'omegle', 'grindr',

        // Meme or online slang
        'simp', 'incel', 'spadek', 'e-girl', 'e-boy', 'irl sex', 'twerk', 'hookup', 'linkup', 'bussin', 'riz',
        'slay', 'sus', 'smash', 'nudes', 'seme god', 'yeet', 'daddy chill', 'r34', 'deepfake', 'titty drop',

        // Leetspeak
        'n1gga', 'sp4nk', 'b1tch', 's3x', 'sh1t', 'd1ck', 'c0ck', 'a55', 'c0on', 'fuk', 'fuq', 'phuck',
        'sh!t', 'azz', 'pusy', 'biatch', 'btch', 'cnt', 'dik', 'coochie', 'vaj', 'vag', 'vijay', 'nutting', 'sl00t', 'id1ot', 'dumb8',

        // Obfuscated words
        'f.u.c.k', 'd.i.c.k', 'b.i.t.c.h', 'c.u.n.t', 'p.u.s.s.y', 'n.u.d.e',
        's.h.i.t', 'f.u.k', 'f.u.q', 'b.i.t.t.c.h', 'c.u.n.t', 'p.u.s.s.y','Ski'

        // Emojis that imply offensive meanings
        '💦', '😘', '🍑', '🍆', '👅', '😩', '👙', '💋', '🔥', '😈',

        // Ableist language
        'schizo', 'psycho', 'crazy', 'lunatic', 'insane', 'mental',

        // Misc / newly added
        'wank', 'boner', 'hardon', 'splooge', 'panties', 'g-spot', 'clit', 'cameltoe',
        'knockers', 'jugs', 'jizz sock', 'chode', 'stegma', 'topless', 'reddit', 'mo nut',
        'edging', 'nipple fetish', 'kissyfish', 'suckit', 'spanker', 'spooner', 'panty sniff',
        'feet pics', 'doggystyle', 'backdoor', 'gash', 'tooty call', 'sugar daddy', 'sugar baby',
        'noodz', 'milkers', 'tiddies', 'hentai', 'tentacle', 'anime thighs', 'vore', 'yiff',
        'teats', 'meatspin', 'stepbro', 'brazzers', 'bangbros', 'diddy','Chicken jockey',
    ];

    $text = strtolower($text);

    foreach ($profanities as $bad) {
        $pattern = '/\b' . preg_quote($bad, '/') . '\b/i';  // Match whole words case-insensitively
        if (preg_match($pattern, $text)) {
            return true;
        }
    }

    return false;
}
?>