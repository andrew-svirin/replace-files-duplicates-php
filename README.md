# replace-files-dupliactes-php
Replace files duplicates by links.

Script oriented to scan directories for files those are equal and replace newest by hard or soft link on older one.
Hard link allow to remove parent file without impact on linked instance, but modification of file or linked instance have effect on both.
Useful tool for decrease size of storage by removing copies.
