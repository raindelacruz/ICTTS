<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="page-heading request-heading mb-3">
            <img src="<?= asset('img/logo-nfa-da.jpg') ?>" alt="Department of Agriculture and National Food Authority logo" class="request-logo brand-mark" width="76" height="76">
            <div>
                <h1 class="h3 mb-1">ICTSD Service Request</h1>
                <p class="text-muted mb-0">Submit ICTSD-related requests to the technical support team.</p>
            </div>
        </div>
        <form class="card shadow-sm" method="post" action="<?= url('request') ?>" enctype="multipart/form-data" data-confirm="Submit this ICTSD request?">
            <div class="card-body p-4">
                <?= csrf_field() ?>
                <p class="text-muted small mb-3"><span class="text-danger">*</span> Required fields</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Request for when <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="requested_for" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input name="requester_name" class="form-control" required maxlength="160">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Position</label>
                        <input name="requester_position" class="form-control" maxlength="160">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email address <span class="text-danger">*</span></label>
                        <input type="email" name="requester_email" class="form-control" required maxlength="190">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact number</label>
                        <input name="requester_contact" class="form-control" maxlength="30" pattern="[0-9+\-\s().]{7,30}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Region <span class="text-danger">*</span></label>
                        <select name="region_id" id="regionSelect" class="form-select" required>
                            <option value="">Select region</option>
                            <?php foreach ($regions as $region): ?>
                                <option value="<?= (int) $region['id'] ?>"><?= e($region['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Branch/Office <span class="text-danger">*</span></label>
                        <select name="office_id" id="officeSelect" class="form-select" required disabled>
                            <option value="">Select branch/office</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category of Request <span class="text-danger">*</span></label>
                        <select name="service_category_id" id="categorySelect" class="form-select" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Specific Request <span class="text-danger">*</span></label>
                        <select name="service_item_id" id="serviceSelect" class="form-select" required disabled>
                            <option value="">Select specific request</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description of Request <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="5" required minlength="10" maxlength="5000"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Attachments</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx">
                        <div class="form-text">Accepted: JPG, PNG, WEBP, PDF, DOC, DOCX. Maximum 5 MB per file.</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button class="btn btn-primary">Submit Request</button>
            </div>
        </form>
    </div>
</div>
