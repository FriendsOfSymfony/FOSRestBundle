# -*- coding: utf-8 -*-

import sys, os
from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer

project = 'FriendsOfSymfony / FOSRestBundle'
author = 'FriendsOfSymfony community'

extensions = [
    'sphinx_tabs.tabs'
]

# This will be used when using the shorthand notation
highlight_language = 'php'

# enable highlighting for PHP code not between <?php ... ?> by default
lexers['php'] = PhpLexer(startinline=True)
