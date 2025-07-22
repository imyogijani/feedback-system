<footer class="content-footer footer bg-footer-theme">
    <div
        class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
        <div class="mb-2 mb-md-0">
            ©
            <script>
                document.write(new Date().getFullYear());
            </script>
            , made with ❤️ by
            <a
                href="https://aksharrajinfotech.com"
                target="_blank"
                class="footer-link fw-bolder"
                style="text-decoration: none;">AksharRaj Infotech</a>
        </div>
    </div>
</footer>
   <!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="profileModalLabel">My Profile</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row">
          <div class="col-md-4 text-center mb-3">
            <img src="<?= htmlspecialchars($profileImage ?? 'assets/images/default_profile.png') ?>" alt="Profile" class="img-thumbnail rounded-circle shadow" style="width:150px;height:150px;">
          </div>
          <div class="col-md-8">
            <table class="table table-borderless">
              <tr>
                <th>Name:</th>
                <td><?= htmlspecialchars($profileData['username'] ?? 'N/A') ?></td>
              </tr>
              <tr>
                <th>Email:</th>
                <td><?= htmlspecialchars($profileData['email'] ?? 'N/A') ?></td>
              </tr>
              <tr>
                <th>Mobile:</th>
                <td><?= htmlspecialchars($profileData['mobile'] ?? 'N/A') ?></td>
              </tr>
              <tr>
                <th>Role:</th>
                <td><?= htmlspecialchars($profileData['role_name'] ?? 'User') ?></td>
              </tr>
              <tr>
                <th>Business:</th>
                <td><?= htmlspecialchars($profileData['business_name'] ?? 'N/A') ?></td>
              </tr>
              <tr>
                <th>Joined:</th>
                <td><?= htmlspecialchars(date('d-m-Y', strtotime($profileData['created_at'] ?? date('Y-m-d')))) ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
       <a href="edit_profile.php" class="btn btn-primary">
    <i class="fa-solid fa-user-edit"></i> Edit Profile
</a>

</button>

        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>