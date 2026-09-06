# Deels: homepage and challenge design pilot

The pilot uses the existing Laravel views, controllers and contest services.
It does not replace the application with the separate React design repository.

## Activation on the test server

Set `HOME_DESIGN_V2=true` in the test application's environment and clear the
configuration/view caches after deploying this branch. This selects Home V2 and
the new public challenge styling. With the flag disabled, the public challenge
keeps its previous presentation. `/home-v2-preview` remains restricted to a full
administrator and does not enable the public design.

The target test server is `http://5.183.191.139:8791`. No working deployment
connection to that server is supplied by this change. Do not deploy to `deels.ru`.

## Existing connections preserved

| Interface | Existing source or action |
| --- | --- |
| Home content | `HomePageDataService`, Home card resources |
| Bank | `coins_bank`; retain the last value on an invalid response, retry on request |
| Challenge page | `deels.public.challenges.show`, `Api/ChallengeController@show` |
| Participation | `ContestParticipationService`; existing join/rejoin/leave POST routes |
| Result submission | `ContestReportingService`; story, button and numeric reporting controls |
| Invitations | Existing user search and invitation POST routes |
| Author controls | Existing edit, useful story and winner selection actions |
| Stories, paid access | Existing preview routes and story modal |
| Results | Existing winners, useful content, gallery and reporting journal |

The overview was extracted into `challenges.partials.contest_overview`; callers
that do not pass `deelsStudio` retain the old labels and controls. The pilot
adds a login action for guests, clearer reporting limits and a visible entry
cost. It preserves server decisions about eligibility and finished contests.

## Validation and limits

`ChallengeStudioViewTest` renders the real partial and checks guest, participant,
finished, author, invitation, reporting-limit and paid-entry behavior. The Home V2
workflow runs each PHPUnit file separately, so PHPUnit 9 does not silently skip
the later positional file arguments. Full-admin preview checks assert the HTTP
status rather than PHP's exception code.

The private design preview contains synthetic data and never performs login,
participation, uploads or payments. It is for reviewing the homepage and three
challenge states. It does not establish that the VPS is updated or that end-to-end
payment, moderation and account flows have passed a release check.

## Layout refinement

The homepage and public challenge share a 1280px content width, responsive page
gutters, type scale and control sizes. Battle cards now follow the main page grid.
Card titles and descriptions have bounded line counts, while action rows align
through flex layout. The actual DEELS SVG replaces the CSS lettermark.

Stories and campaigns use the same independent collection switcher. Without
JavaScript, every collection remains available; with JavaScript, only the selected
collection is shown. Keyboard navigation and links to a specific collection select
the correct panel. Campaign categories remain real navigation links. The bank and
participation explanation follow the main content feed. On the challenge page,
the task precedes the conditions visually; eligibility, payments and reporting
actions still come from the existing services.
