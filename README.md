# DACEM Sync

Synchronizes DACEM content types with OCCAPI-serialized entities.

## Installation

Include the repository in your project's `composer.json` file:

    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "https://github.com/EuropeanUniversityFoundation/dacem_sync"
        }
    ],

Then you can require the package as usual:

    composer require euf/dacem_sync

Finally, install the module:

    drush en dacem_sync
