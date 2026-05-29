<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <?php if ($ticket): ?>
                    <h1 class="h4 mb-3">Confirm Ticket Completion</h1>
                    <dl class="row">
                        <dt class="col-sm-4">Ticket No</dt><dd class="col-sm-8"><?= e($ticket['ticket_no']) ?></dd>
                        <dt class="col-sm-4">Request</dt><dd class="col-sm-8"><?= e($ticket['service_name']) ?></dd>
                        <dt class="col-sm-4">Assigned To</dt><dd class="col-sm-8"><?= e($ticket['assigned_to_name'] ?? 'Unassigned') ?></dd>
                        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><?= status_badge($ticket['status']) ?></dd>
                    </dl>
                    <form method="post" action="<?= url('confirm/' . $token) ?>" data-confirm="Confirm that this ICTSD request has been completed?">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Was the issue resolved?</label>
                            <select name="resolved_yes_no" class="form-select" required>
                                <option value="yes">Yes, resolved</option>
                                <option value="no">No, return for further action</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Satisfaction Rating</label>
                            <select name="rating" class="form-select">
                                <option value="5">5 - Very satisfied</option>
                                <option value="4">4 - Satisfied</option>
                                <option value="3">3 - Neutral</option>
                                <option value="2">2 - Unsatisfied</option>
                                <option value="1">1 - Very unsatisfied</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea name="feedback_comments" class="form-control" rows="3" maxlength="5000"></textarea>
                        </div>
                        <button class="btn btn-success">Submit Feedback</button>
                    </form>
                <?php else: ?>
                    <h1 class="h4">Confirmation Link Unavailable</h1>
                    <p class="text-muted mb-0">This link is invalid, expired, or already used.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
