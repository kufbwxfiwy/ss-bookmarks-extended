# SS Bookmarks Extended
A single script PHP bookmarks manager. No database and no additional files needed. Original work was by Dominic Manley.

Important points when using this script:

1. Because of the heredoc synthax used DO NOT mess with whitespace (where the heredoc is being used).

2. An 'uploads' folder must exist beforehand. If this folder is absent uploads will fail.

3. Duplicate links will be eliminated within the same tag. They will not if they are within separate tags.

4. The sorting order of the bookmarks will be preserved in downloads.
