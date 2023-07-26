# WP-CLI Dump Command

This repo adds a `dump` command to WP-CLI. It allows you to dump multiple things from your WordPress installation into a single file. The following things can be dumped:
- Database
- Plugins
- Must-Use Plugins
- Themes
- Or the entire `wp-content` directory

## TODOs and ideas

List of all commands with hierarchy:

- [ ] wp dump export all ( generate a zip with all dumps below )
    - [x] wp dump export database
    - [x] wp dump export themes
    - [x] wp dump export plugins
    - [ ] wp dump export uploads
    - [ ] wp dump export languages
- [ ] wp dump import all ( import a generated zip by the "export all" command )
    - [ ] wp dump import database
    - [ ] wp dump import themes
    - [ ] wp dump import plugins
    - [ ] wp dump import uploads
    - [ ] wp dump import languages  

---
Todos

- [ ] Find a way to implements all these commands with DRY as possible
- [x] Maybe use WP_List_Table to list the dumps in option page
  - [x] Add a button to download the dump
  - [ ] Add a button to delete the dump
  - [ ] The list should be paginated
  - [ ] The list must have filters by their type (database, plugins, themes, uploads, languages)
  - [ ] The list must have a search input
  - [ ] The list must handle bulk actions (delete, download)
  - [x] Each dump must have a column with the date of creation
- [ ] Make sure the dumps are deleted in X days
  - [ ] Add a setting to change the number of days in a hook or in the option page
- [ ] Check if it's possible to replace the table prefix in the database dump
- [ ] Add an option to perform a search-replace in the database dump ( create a backup, perform the search-replace, create the dump, reimport the backup and then delete the backup )

---
SECURITY
- [ ] Check if it's possible to get the link of the dump file by guessing the name and download it
  - [ ] If yes, try to find a way to prevent it
  - [ ] Maybe need to change the way to download the file from the admin area
- [ ] When the plugin is deleted, delete all the dumps ( Not on deactivation to prevent bad behavior )