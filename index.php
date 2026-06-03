<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$currentUser = getCurrentUser();
$userRole = $currentUser['role'];
$search = trim($_GET['search'] ?? '');
if (mb_strlen($search) > 100) {
    $search = mb_substr($search, 0, 100);
}
$page = max(1, (int)($_GET['page'] ?? 1));
$allowedPerPage = [5, 10, 25];
$perPage = (int)($_GET['per_page'] ?? 5);
$perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 5;
$allowedSorts = ['newest', 'oldest', 'author_a_z', 'author_z_a'];
$sort = $_GET['sort'] ?? 'newest';
$sort = in_array($sort, $allowedSorts, true) ? $sort : 'newest';

$orderClause = match ($sort) {
    'oldest' => ' ORDER BY created_at ASC',
    'author_a_z' => ' ORDER BY author ASC',
    'author_z_a' => ' ORDER BY author DESC',
    default => ' ORDER BY created_at DESC',
};

$searchTerm = '%' . $search . '%';
if ($search !== '') {
    $countStmt = $mysqli->prepare('SELECT COUNT(*) FROM posts WHERE title LIKE ? OR content LIKE ? OR author LIKE ?');
    $countStmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
} else {
    $countStmt = $mysqli->prepare('SELECT COUNT(*) FROM posts');
}
$countStmt->execute();
$countStmt->bind_result($totalPosts);
$countStmt->fetch();
$countStmt->close();

$totalPages = max(1, (int)ceil((int)$totalPosts / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$posts = [];
if ((int)$totalPosts > 0) {
    if ($search !== '') {
        $stmt = $mysqli->prepare('SELECT id, title, content, author, user_id, created_at FROM posts WHERE title LIKE ? OR content LIKE ? OR author LIKE ?' . $orderClause . ' LIMIT ? OFFSET ?');
        $stmt->bind_param('sssii', $searchTerm, $searchTerm, $searchTerm, $perPage, $offset);
    } else {
        $stmt = $mysqli->prepare('SELECT id, title, content, author, user_id, created_at FROM posts' . $orderClause . ' LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $perPage, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $stmt->close();
}

$stmt = $mysqli->prepare('SELECT COUNT(*) AS total_posts, COUNT(DISTINCT author) AS total_authors, MAX(created_at) AS latest_post, SUM(created_at >= CURDATE()) AS posts_today FROM posts');
$stmt->execute();
$dashboard = $stmt->get_result()->fetch_assoc() ?: [];
$stmt->close();

$stmt = $mysqli->prepare('SELECT author, COUNT(*) AS post_count FROM posts GROUP BY author ORDER BY post_count DESC LIMIT 5');
$stmt->execute();
$topAuthors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $mysqli->prepare('SELECT DATE(created_at) AS publish_date, COUNT(*) AS count FROM posts WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY publish_date ASC');
$stmt->execute();
$recentDays = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function buildPageUrl(int $page, string $search, string $sort, int $perPage): string
{
    $query = ['page' => $page, 'sort' => $sort, 'per_page' => $perPage];
    if ($search !== '') {
        $query['search'] = $search;
    }
    return '?' . http_build_query($query);
}

function highlightSearchTerm(string $text, string $search): string
{
    $escaped = e($text);
    if ($search === '') {
        return $escaped;
    }
    foreach (array_filter(preg_split('/\s+/', $search)) as $keyword) {
        $escaped = preg_replace('/(' . preg_quote(e($keyword), '/') . ')/i', '<mark class="mark-highlight">$1</mark>', $escaped);
    }
    return $escaped;
}
?>

<?php include __DIR__ . '/includes/header.php'; ?>

<main class="container mt-4">
    <?php if (isset($_GET['msg']) && $_GET['msg'] !== ''): ?>
        <div class="alert alert-<?php echo e(($_GET['type'] ?? '') === 'danger' ? 'danger' : 'success'); ?> alert-dismissible fade show" role="alert">
            <?php echo e($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="dashboard-header p-4 rounded-4 shadow-sm mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-6 mb-2">Blog Management Dashboard</h1>
                <p class="lead text-secondary mb-0">Search posts, review analytics, and manage content based on your role.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <?php if (in_array($userRole, ['admin', 'editor'], true)): ?>
                    <a href="create.php" class="btn btn-lg btn-primary needs-loading"><i class="bi bi-plus-lg me-2"></i>Add New Post</a>
                <?php else: ?>
                    <span class="badge bg-secondary fs-6">Viewer: read-only access</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row gx-3 gy-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="card stats-card shadow-sm h-100"><div class="card-body"><p class="text-uppercase text-muted small mb-1">Total Posts</p><h2 class="card-title mb-0"><?php echo number_format((int)($dashboard['total_posts'] ?? 0)); ?></h2></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card stats-card shadow-sm h-100"><div class="card-body"><p class="text-uppercase text-muted small mb-1">Total Authors</p><h2 class="card-title mb-0"><?php echo number_format((int)($dashboard['total_authors'] ?? 0)); ?></h2></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card stats-card shadow-sm h-100"><div class="card-body"><p class="text-uppercase text-muted small mb-1">Latest Post Date</p><h2 class="card-title mb-0"><?php echo !empty($dashboard['latest_post']) ? e(date('M d, Y', strtotime($dashboard['latest_post']))) : 'No posts'; ?></h2></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card stats-card shadow-sm h-100"><div class="card-body"><p class="text-uppercase text-muted small mb-1">Posts Added Today</p><h2 class="card-title mb-0"><?php echo number_format((int)($dashboard['posts_today'] ?? 0)); ?></h2></div></div></div>
    </div>

    <div class="row gx-3 gy-3 mb-4">
        <div class="col-lg-6"><div class="card chart-card shadow-sm h-100"><div class="card-body"><h5 class="mb-0">Top Authors</h5><p class="text-muted small">Posts by author</p><canvas id="authorChart" height="260"></canvas></div></div></div>
        <div class="col-lg-6"><div class="card chart-card shadow-sm h-100"><div class="card-body"><h5 class="mb-0">Recent Activity</h5><p class="text-muted small">Posts created in the last 7 days</p><canvas id="recentChart" height="260"></canvas></div></div></div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center gy-3">
                <div class="col-md-8"><h5 class="mb-2">Search Posts</h5><p class="text-muted mb-0">Search validates input length and uses prepared statements.</p></div>
                <div class="col-md-4 text-md-end"><span class="badge bg-primary">Showing <?php echo (int)$totalPosts; ?> result<?php echo (int)$totalPosts === 1 ? '' : 's'; ?></span></div>
            </div>
            <form class="row g-2 align-items-center mt-3 search-form secure-form" method="get" action="" novalidate>
                <div class="col-12 col-md-5">
                    <div class="input-group search-input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" class="form-control search-input" placeholder="Search posts" value="<?php echo e($search); ?>" maxlength="100" data-validate="search">
                        <?php if ($search !== ''): ?><button type="button" class="btn btn-outline-secondary clear-search" data-bs-toggle="tooltip" title="Clear search"><i class="bi bi-x-lg"></i></button><?php endif; ?>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-6 col-md-2"><select name="per_page" class="form-select form-select-lg"><?php foreach ([5, 10, 25] as $n): ?><option value="<?php echo $n; ?>" <?php echo $perPage === $n ? 'selected' : ''; ?>><?php echo $n; ?> per page</option><?php endforeach; ?></select></div>
                <div class="col-6 col-md-3"><select name="sort" class="form-select form-select-lg"><option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest first</option><option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest first</option><option value="author_a_z" <?php echo $sort === 'author_a_z' ? 'selected' : ''; ?>>Author A-Z</option><option value="author_z_a" <?php echo $sort === 'author_z_a' ? 'selected' : ''; ?>>Author Z-A</option></select></div>
                <div class="col-12 col-md-2 text-md-end"><button type="submit" class="btn btn-primary needs-loading"><i class="bi bi-search me-1"></i> Search</button></div>
            </form>
        </div>
    </div>

    <?php if ((int)$totalPosts === 0): ?>
        <div class="card empty-state-card text-center py-5 shadow-sm"><div class="card-body"><div class="mb-3 display-4">No posts</div><h3 class="mb-2">No posts available</h3><?php if (in_array($userRole, ['admin', 'editor'], true)): ?><a href="create.php" class="btn btn-primary btn-lg needs-loading">Create Your First Post</a><?php endif; ?></div></div>
    <?php else: ?>
        <div class="card shadow-sm"><div class="card-body"><div class="table-responsive"><table class="table table-hover table-striped align-middle mb-0"><thead class="table-light"><tr><th>ID</th><th>Title</th><th>Excerpt</th><th>Author</th><th>Created Date</th><th class="text-end">Actions</th></tr></thead><tbody>
            <?php foreach ($posts as $row): ?>
                <tr>
                    <td><?php echo (int)$row['id']; ?></td>
                    <td><?php echo highlightSearchTerm($row['title'], $search); ?></td>
                    <td><?php echo nl2br(highlightSearchTerm(strlen($row['content']) > 120 ? substr($row['content'], 0, 120) . '...' : $row['content'], $search)); ?></td>
                    <td><?php echo highlightSearchTerm($row['author'], $search); ?></td>
                    <td><?php echo e(date('M d, Y', strtotime($row['created_at']))); ?></td>
                    <td class="text-end">
                        <?php if ($userRole === 'admin' || ($userRole === 'editor' && (int)$row['user_id'] === (int)$currentUser['id'])): ?>
                            <a href="edit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="tooltip" title="Edit post"><i class="bi bi-pencil-square"></i></a>
                        <?php endif; ?>
                        <?php if ($userRole === 'admin'): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-button" data-id="<?php echo (int)$row['id']; ?>" data-title="<?php echo e($row['title']); ?>" data-bs-toggle="tooltip" title="Delete post"><i class="bi bi-trash"></i></button>
                        <?php endif; ?>
                        <?php if ($userRole === 'viewer'): ?><span class="text-muted small">Read only</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody></table></div></div></div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Post pagination" class="mt-4"><ul class="pagination justify-content-center flex-wrap">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>"><a class="page-link needs-loading" href="<?php echo e(buildPageUrl(1, $search, $sort, $perPage)); ?>">First</a></li>
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>"><a class="page-link needs-loading" href="<?php echo e(buildPageUrl(max(1, $page - 1), $search, $sort, $perPage)); ?>">Previous</a></li>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?><li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link needs-loading" href="<?php echo e(buildPageUrl($i, $search, $sort, $perPage)); ?>"><?php echo $i; ?></a></li><?php endfor; ?>
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>"><a class="page-link needs-loading" href="<?php echo e(buildPageUrl(min($totalPages, $page + 1), $search, $sort, $perPage)); ?>">Next</a></li>
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>"><a class="page-link needs-loading" href="<?php echo e(buildPageUrl($totalPages, $search, $sort, $perPage)); ?>">Last</a></li>
            </ul></nav>
        <?php endif; ?>
    <?php endif; ?>
</main>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 shadow-sm"><div class="modal-header border-bottom-0"><h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p class="mb-0">Are you sure you want to delete this post?</p><p class="fw-semibold mt-2" id="deleteModalTitle"></p></div><div class="modal-footer border-top-0"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><form id="deleteModalForm" method="post" action="delete.php" class="m-0"><input type="hidden" name="id" id="deletePostId" value=""><input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>"><button type="submit" class="btn btn-danger">Delete Post</button></form></div></div></div>
</div>

<script>
window.chartStats = {
    authorLabels: <?php echo json_encode(array_column($topAuthors, 'author')); ?>,
    authorCounts: <?php echo json_encode(array_map('intval', array_column($topAuthors, 'post_count'))); ?>,
    dateLabels: <?php echo json_encode(array_map(static fn($item) => date('M d', strtotime($item['publish_date'])), $recentDays)); ?>,
    dateCounts: <?php echo json_encode(array_map('intval', array_column($recentDays, 'count'))); ?>,
};
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
