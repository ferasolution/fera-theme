# Maintainer build and release workflow

The canonical independent source is wordpress/fera-foundry. Do not run older template/theme generators against this directory. They are reference scaffolds, not release tools.

The delivered ZIP already contains every runnable file and needs no build tools on WordPress. For future local source maintenance only: tools/wordpress-test/compile-foundry.mjs regenerates minified siblings from readable source. tools/package-foundry.py scans runtime source, normalizes archive paths, validates ZIP integrity, and writes releases/fera-solution-foundry.zip plus its SHA-256 inventory. Install that exact ZIP into clean WordPress and rerun checks before release. Never modify the ZIP by hand.

Keep a recoverable source snapshot and inspect existing edits before changes. No production deployment is performed by these scripts. tools/install-foundry-test.py is guarded to the disposable localhost staging fixture and is not a production migration tool. The activation/setup importer preserves owned edited pages and unrelated data. Backup and reviewed URL mapping are required before retiring the previous site.
