<?php
$activeMenu  = $data['active_menu'] ?? 'landing';
$hideDefaultNav = !empty($data['hide_default_nav']);
$isSaaSWorkspace = (strpos($activeMenu, 'admin') === 0 || strpos($activeMenu, 'petugas') === 0) && !$hideDefaultNav;
?>

<?php if ($isSaaSWorkspace): ?>
    </main>
    <footer class="py-3 px-4 border-top bg-white">
      <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 text-muted" style="font-size: 0.78rem;">
        <div><strong class="text-dark">Nutrition UNKLAB</strong> • <?php echo htmlspecialchars(APP_INSTITUTION); ?></div>
        <div>Gemini Vision AI • HITL Threshold <?php echo defined('AI_CONFIDENCE_THRESHOLD') ? AI_CONFIDENCE_THRESHOLD : '70'; ?>%</div>
      </div>
    </footer>
  </div><!-- /.saas-main -->
</div><!-- /.saas-shell -->
<?php else: ?>
</main>

<!-- MODERN SAAS MINIMALIST FOOTER -->
<footer class="mt-auto py-4 bg-white border-top">
  <div class="container" style="max-width: 1000px;">
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 text-muted" style="font-size: 0.8rem;">
      <div class="d-flex align-items-center gap-2">
        <span class="fw-semibold text-dark">Nutrition UNKLAB</span>
        <span class="text-black-50">•</span>
        <span><?php echo htmlspecialchars(APP_INSTITUTION); ?>, Airmadidi</span>
      </div>
      <div>
        Gemini Vision AI <span class="mx-1">·</span> Kalibrasi HITL <?php echo defined('AI_CONFIDENCE_THRESHOLD') ? AI_CONFIDENCE_THRESHOLD : '70'; ?>%
      </div>
    </div>
  </div>
</footer>
<?php endif; ?>

<script src="<?php echo APP_PATH; ?>/public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
