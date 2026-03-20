
<?php if (!empty($flash)): ?>
  <?php
    $color = [
        'success' => 'bg-green-50 text-green-800 border-green-300',
        'error'   => 'bg-red-50 text-red-800 border-red-300',
        'info'    => 'bg-blue-50 text-blue-800 border-blue-300',
    ][$flash['type']] ?? 'bg-gray-50 text-gray-800 border-gray-300';
  ?>
  <div class="border <?= $color ?> rounded-xl px-4 py-3 mb-4">
      <?= htmlspecialchars($flash['message']) ?>
  </div>
<?php endif; ?>
