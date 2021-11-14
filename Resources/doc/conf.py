# -*- coding: utf-8 -*-

import sys, os

project = 'FriendsOfSymfony / FOSRestBundle'
author = 'FriendsOfSymfony community'

# -- General configuration -----------------------------------------------------

# Add any Sphinx extension module names here, as strings. They can be extensions
# coming with Sphinx (named 'sphinx.ext.*') or your custom ones.
extensions = ['sensio.sphinx.configurationblock', 'sensio.sphinx.phpcode']

# Add any paths that contain templates here, relative to this directory.
templates_path = ['_templates']

# The suffix of source filenames.
source_suffix = '.rst'

# The master toctree document.
master_doc = 'index'

# List of patterns, relative to source directory, that match files and
# directories to ignore when looking for source files.
exclude_patterns = ['_build']

# The name of the Pygments (syntax highlighting) style to use.
pygments_style = 'sphinx'

# This will be used when using the shorthand notation
highlight_language = 'php'
