      </div> <!-- End of container fluid -->
    </main>
    
    <footer class="footer pt-3">
      <div class="container-fluid">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-lg-6 mb-lg-0 mb-4">
            <div class="copyright text-center text-sm text-muted text-lg-start">
              © <script>
                document.write(new Date().getFullYear())
              </script> - Techie Admin
            </div>
          </div>
          <div class="col-lg-6">
            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
              <li class="nav-item">
                <a href="../index.php" class="nav-link text-muted" target="_blank">Site principal</a>
              </li>
              <li class="nav-item">
                <a href="docs/documentation.html" class="nav-link text-muted" target="_blank">Documentation</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
  </div>
  
  <!-- Core JS Files -->
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="assets/js/plugins/chartjs.min.js"></script>
  
  <!-- Control Center for Argon Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="assets/js/argon-dashboard.min.js"></script>
  
  <!-- Custom form validation script -->
  <script src="assets/js/form-validation.js"></script>
  
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
</body>
</html> 