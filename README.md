# Museum of Failed Projects

A Laravel museum where creators exhibit unfinished work and other members can request permission to revive it. The submission branch is `codex/museum-workflows`.

## Install locally

Use PHP 8.5 with PDO SQLite, Composer 2, Node.js 22.12 or newer, npm and Git. From a terminal:

```sh
git clone --branch codex/museum-workflows https://github.com/StiopE/museum-of-lost-projects.git
cd museum-of-lost-projects
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm ci
npm run build
php artisan serve
```

Open the address printed by Artisan. The default environment is local, with SQLite and log-based email delivery. Run `php artisan queue:work` in a second terminal to process queued adoption notifications. Verification and adoption messages appear in `storage/logs/laravel.log`. To reset a **disposable grading database**, run `php artisan migrate:fresh --seed`. This deletes all its existing records. Run `composer ci:check` for formatting, static analysis and the test suite.

## Assessment accounts

All three seeded local accounts use the password `password`.

| Account | Use |
| --- | --- |
| admin@example.com | Dashboard → Manage categories or Review submissions. Also owns The Night Bus Atlas. |
| explorer@example.com | Mara Vale, the explorer for the live demo. |
| tomas@museum.example.test | Tomas Reed, creator of Rain Ticket Press. |

Creators can publish directly or submit a draft for curator review. A submitted exhibit is private until a curator publishes it. Curators can also return it to draft. Images are ordered by their numeric display order, with older IDs first on ties. A category must be empty before deletion.

## Five-minute presentation

[Download the five slides](output/pdf/museum-presentation.pdf). Before presenting, open two separate browser profiles in the same local installation, signed in as Mara and Tomas. Start with a freshly seeded demo database.

| Time | Slide and action |
| --- | --- |
| 0:00–0:30 | **Museum.** Open Rain Ticket Press from the homepage. Explain why the creator stopped and why the project is open to a new owner. |
| 0:30–1:15 | **Data model.** Point out the creator, category and progress update. A project has many adoption requests. A successful request links the original, the requester and a new project record. |
| 1:15–3:15 | **Live demo.** As Mara, enter “I’ll keep the gauge outdoors and move the paper roll inside” and send the request. As Tomas, open Dashboard → View adoption requests → Review request and approve Mara. Switch back to Mara, open her sent requests and select Manage revived project. Show its draft status and the original public exhibit. |
| 3:15–4:20 | **Engineering challenge.** Explain how a transaction and row locks prevent two approvals from creating competing revivals. Point to the original project's ID on the new record and the approved request's revival ID. Explain who can approve and why requesters cannot approve their own requests. |
| 4:20–5:00 | **Next step.** Show the curator queue. Explain how private revision notes could help a creator revise a returned submission, and why the notes would need to identify the reviewed version. |

After a rehearsal, use a fresh disposable installation for the graded demo. Approval closes adoption on Rain Ticket Press.
