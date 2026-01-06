SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE workspaces;
TRUNCATE users;
TRUNCATE teams;
TRUNCATE team_members;
TRUNCATE model_has_roles;
TRUNCATE lead_sources;
TRUNCATE lead_tags;
TRUNCATE lead_costs;
TRUNCATE lead_goals;
TRUNCATE lead_contact;
TRUNCATE lead_kanban;
TRUNCATE lead_lead_tag;
TRUNCATE portfolios;
TRUNCATE proposals;

INSERT INTO workspaces (
    id,
    name,
    slug,
    owner_id,
    created_at,
    updated_at
)
SELECT
    ww.id,
    ww.name,
    CONCAT(ww.id, ww.name),
    ww.owner_id,
    ww.created_at,
    ww.updated_at
FROM writeverto_workspace ww
WHERE ww.id NOT IN (SELECT id FROM workspaces);

INSERT INTO users (
    id,
    name,
    email,
    password,
    created_at,
    updated_at
)
SELECT
    au.id,
    CONCAT_WS(' ', au.first_name, au.last_name),
    au.email,
    au.password,
    au.date_joined,
    au.date_joined
FROM auth_user au
WHERE au.id NOT IN (SELECT id FROM users);

UPDATE users u
JOIN workspaces w ON w.owner_id = u.id
SET u.workspace_id = w.id, password="$2y$12$pVA3qNj0ZsCXqwCSluhUDusLwweGYPZeTpRl0dZIlB6xAWU79eboW", email_verified_at = now()
WHERE u.workspace_id IS NULL;

INSERT INTO teams (
    id,
    name,
    workspace_id,
    created_by_id,
    created_at,
    updated_at
)
SELECT
    wt.id,
    wt.name,
    wt.workspace_id,
    wt.owner_id,
    wt.created_at,
    wt.updated_at
FROM writeverto_team wt
WHERE wt.id NOT IN (SELECT id FROM teams) AND workspace_id IS NOT NULL;


INSERT INTO team_members (
    team_id,
    user_id,
    role,
    status,
    email,
    joined_at,
    created_at,
    updated_at
)
SELECT
    tm.team_id,
    tm.user_id,
    tm.role,
    tm.status,
    tm.email,
    tm.joined_at,
    NOW(),
    NOW()
FROM writeverto_teammembership tm
WHERE NOT EXISTS (
    SELECT 1
    FROM team_members lm
    WHERE lm.team_id = tm.team_id
      AND lm.user_id = tm.user_id
)  AND user_id IS NOT NULL;




INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT DISTINCT
    1 AS role_id,
    'App\\Models\\User' AS model_type,
    tm.user_id AS model_id
FROM writeverto_teammembership tm
WHERE tm.role = 'owner'
  AND tm.user_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM model_has_roles mhr
      WHERE mhr.role_id = 1
        AND mhr.model_type = 'App\\Models\\User'
        AND mhr.model_id = tm.user_id
  );

INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT DISTINCT
    3 AS role_id,
    'App\\Models\\User' AS model_type,
    tm.user_id AS model_id
FROM writeverto_teammembership tm
WHERE tm.role = 'member'
  AND tm.user_id IS NOT NULL
  AND tm.user_id NOT IN (
      SELECT user_id
      FROM writeverto_teammembership
      WHERE role = 'owner'
        AND user_id IS NOT NULL
  )
  AND NOT EXISTS (
      SELECT 1
      FROM model_has_roles mhr
      WHERE mhr.model_type = 'App\\Models\\User'
        AND mhr.model_id = tm.user_id
  );



INSERT INTO lead_sources (
    id,
    name,
    description,
    color,
    sort_order,
    is_active,
    team_id,
    created_at,
    updated_at
)
SELECT
    wls.id,
    wls.name,
    wls.description,
    wls.color,
    wls.sort_order,
    wls.is_active,
    wls.team_id,
    wls.created_at,
    wls.updated_at
FROM writeverto_leadsource wls
WHERE wls.id NOT IN (SELECT id FROM lead_sources);



INSERT INTO lead_tags (
    id,
    name,
    description,
    color,
    sort_order,
    is_active,
    team_id,
    created_at,
    updated_at
)
SELECT
    wlt.id,
    wlt.name,
    wlt.description,
    wlt.color,
    wlt.sort_order,
    wlt.is_active,
    wlt.team_id,
    wlt.created_at,
    wlt.updated_at
FROM writeverto_leadtag wlt
WHERE wlt.id NOT IN (SELECT id FROM lead_tags);


INSERT INTO lead_costs (
    id,
    title,
    monthly_cost,
    is_active,
    member_id,
    team_id,
    source_id,
    created_at,
    updated_at
)
SELECT
    wlc.id,
    wlc.title,
    wlc.monthly_cost,
    wlc.is_active,
    wlc.member_id,
    wlc.team_id,
    wlc.source_id,
    wlc.created_at,
    wlc.updated_at
FROM writeverto_leadcost wlc
WHERE wlc.id NOT IN (SELECT id FROM lead_costs);

INSERT INTO lead_kanban (
    id,
    name,
    color,
    sort_order,
    is_active,
    team_id,
    code,
    is_system,
    created_at,
    updated_at
)
SELECT
    wtk.id,
    wtk.name,
    wtk.color,
    wtk.sort_order,
    wtk.is_active,
    wtk.team_id,
    wtk.code,
    wtk.is_system,
    wtk.created_at,
    wtk.updated_at
FROM writeverto_teamleadkanban wtk
WHERE wtk.id NOT IN (SELECT id FROM lead_kanban);



INSERT INTO lead_goals (
    id,
    goal_type,
    period,
    target_value,
    current_value,
    is_active,
    member_id,
    team_id,
    description,
    created_at,
    updated_at
)
SELECT
    wlg.id,
    wlg.goal_type,
    wlg.period,
    wlg.target_value,
    wlg.current_value,
    wlg.is_active,
    wlg.member_id,
    wlg.team_id,
    wlg.description,
    wlg.created_at,
    wlg.updated_at
FROM writeverto_leadgoal wlg
WHERE wlg.id NOT IN (SELECT id FROM lead_goals);


INSERT INTO contacts (
    id,
    company,
    email,
    phone_number,
    website,
    first_name,
    last_name,
    job_title,
    team_id,
    created_at,
    updated_at
)
SELECT
    wc.id,
    wc.company,
    wc.email,
    wc.phone_number,
    wc.website,
    wc.first_name,
    wc.last_name,
    wc.job_title,
    wc.team_id,
    wc.created_at,
    wc.updated_at
FROM writeverto_contact wc
WHERE wc.id NOT IN (SELECT id FROM contacts);


INSERT INTO lead_contact (
    contact_id,
    lead_id,
    created_at,
    updated_at
)
SELECT
    wlc.contact_id,
    wlc.lead_id,
    NOW(),
    NOW()
FROM writeverto_leadcontact wlc
WHERE NOT EXISTS (
    SELECT 1
    FROM lead_contact lc
    WHERE lc.contact_id = wlc.contact_id
      AND lc.lead_id = wlc.lead_id
);


INSERT INTO portfolios (
    id,
    scale,
    keywords,
    title,
    description,
    is_active,
    sort_order,
    created_by_id,
    team_id,
    created_at,
    updated_at
)
SELECT
    wp.id,
    wp.scale,
    wp.keywords,
    wp.title,
    wp.description,
    wp.is_active,
    wp.sort_order,
    wp.user_id,
    wp.team_id,
    wp.created_at,
    wp.updated_at
FROM writeverto_portfolio wp
WHERE wp.id NOT IN (SELECT id FROM portfolios);


INSERT INTO proposals (
    id,
    title,
    description,
    sort_order,
    keywords,
    is_active,
    user_id,
    team_id,
    job_description,
    created_at,
    updated_at
)
SELECT
    wpr.id,
    wpr.title,
    wpr.description,
    wpr.sort_order,
    wpr.keywords,
    wpr.is_active,
    wpr.user_id,
    wpr.team_id,
    wpr.job_description,
    wpr.created_at,
    wpr.updated_at
FROM writeverto_proposal wpr
WHERE wpr.id NOT IN (SELECT id FROM proposals);


INSERT INTO lead_lead_tag (
    lead_id,
    lead_tag_id,
    created_at,
    updated_at
)
SELECT
    wlt.lead_id,
    wlt.leadtag_id,
    NOW(),
    NOW()
FROM writeverto_lead_tags wlt
WHERE NOT EXISTS (
    SELECT 1
    FROM lead_lead_tag llt
    WHERE llt.lead_id = wlt.lead_id
      AND llt.lead_tag_id = wlt.leadtag_id
);
INSERT INTO leads (
    id,
    title,
    description,
    expected_value,
    actual_value,
    cost,
    next_follow_up,
    conversion_date,
    notes,
    assigned_member_id,
    team_id,
    kanban_id,
    source_id,
    is_archived,
    conversion_by_id,
    created_by_id,
    url,
    created_at,
    updated_at
)
SELECT
    wl.id,
    wl.title,
    wl.description,
    wl.expected_value,
    wl.actual_value,
    wl.cost,
    wl.next_follow_up,
    wl.conversion_date,
    wl.notes,
    wl.assigned_member_id,
    wl.team_id,
    wl.stage_id,
    wl.source_id,
    wl.is_archived,
    wl.conversion_by_id,
    wl.created_by_id,
    wl.url,
    wl.created_at,
    wl.updated_at
FROM writeverto_lead wl
WHERE wl.id NOT IN (SELECT id FROM leads);



INSERT INTO analyticscost (
    id,
    month,
    year,
    type,
    avg_cost_per_lead,
    total_cost,
    team_id,
    created_at,
    updated_at
)
SELECT
    wac.id,
    wac.month,
    wac.year,
    wac.type,
    wac.avg_cost_per_lead,
    wac.total_cost,
    wac.team_id,
    wac.created_at,
    wac.updated_at
FROM writeverto_analyticscost wac
WHERE wac.id NOT IN (SELECT id FROM analyticscost);


INSERT INTO analyticsgoal (
    id,
    fullname,
    month,
    year,
    goal_type,
    acheived,
    progress_value,
    target_value,
    team_id,
    user_id,
    created_at,
    updated_at
)
SELECT
    wag.id,
    wag.fullname,
    wag.month,
    wag.year,
    wag.goal_type,
    wag.acheived,
    wag.progress_value,
    wag.target_value,
    wag.team_id,
    wag.user_id,
    wag.created_at,
    wag.updated_at
FROM writeverto_analyticsgoal wag
WHERE wag.id NOT IN (SELECT id FROM analyticsgoal);


INSERT INTO analyticslead (
    id,
    fullname,
    month,
    year,
    total_lead,
    total_won,
    total_lost,
    total_value,
    total_cost,
    total_expected_value,
    total_roi,
    avg_cost_per_lead,
    team_id,
    user_id,
    created_at,
    updated_at
)
SELECT
    wal.id,
    wal.fullname,
    wal.month,
    wal.year,
    wal.total_lead,
    wal.total_won,
    wal.total_lost,
    wal.total_value,
    wal.total_cost,
    wal.total_expected_value,
    wal.total_roi,
    wal.avg_cost_per_lead,
    wal.team_id,
    wal.user_id,
    wal.created_at,
    wal.updated_at
FROM writeverto_analyticslead wal
WHERE wal.id NOT IN (SELECT id FROM analyticslead);


INSERT INTO analyticssource (
    id,
    month,
    year,
    total_cost,
    total_lead,
    total_won,
    total_lost,
    total_value,
    total_expected_value,
    total_roi,
    avg_cost_per_lead,
    title,
    source_id,
    team_id,
    created_at,
    updated_at
)
SELECT
    was.id,
    was.month,
    was.year,
    was.total_cost,
    was.total_lead,
    was.total_won,
    was.total_lost,
    was.total_value,
    was.total_expected_value,
    was.total_roi,
    was.avg_cost_per_lead,
    was.title,
    was.source_id,
    was.team_id,
    was.created_at,
    was.updated_at
FROM writeverto_analyticssource was
WHERE was.id NOT IN (SELECT id FROM analyticssource);


INSERT INTO workspace_credits (
    id,
    transaction_type,
    credits,
    transaction_id,
    note,
    triggered_by_id,
    workspace_id,
    created_at,
    updated_at
)
SELECT
    wc.id,
    wc.transaction_type,
    wc.credits,
    wc.transaction_id,
    wc.note,
    wc.triggered_by_id,
    wc.workspace_id,
    wc.created_at,
    wc.created_at
FROM writeverto_credit wc
WHERE wc.id NOT IN (SELECT id FROM workspace_credits);


INSERT INTO workspace_credits (
    id,
    transaction_type,
    credits,
    transaction_id,
    note,
    triggered_by_id,
    workspace_id,
    created_at,
    updated_at
)
SELECT
    wc.id,
    wc.transaction_type,
    wc.credits,
    wc.transaction_id,
    wc.note,
    wc.triggered_by_id,
    wc.workspace_id,
    wc.created_at,
    wc.created_at
FROM writeverto_credit wc
WHERE wc.id NOT IN (SELECT id FROM workspace_credits);


SET FOREIGN_KEY_CHECKS = 1;