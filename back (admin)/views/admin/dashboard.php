<?php
// Include header
require_once 'views/admin/layout/header.php';

// Get category stats
require_once 'controllers/CategoryController.php';
$categoryController = new CategoryController();
$categoryCount = $categoryController->getStats();
?>

<!-- Cards stats section -->
<div class="row">
  <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
    <div class="card card-futuristic">
      <div class="card-body p-3">
        <div class="row">
          <div class="col-8">
            <div class="numbers">
              <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Projets</p>
              <h5 class="font-weight-bolder"><?php echo $totalProjects; ?></h5>
              <p class="mb-0">
                <span class="text-success text-sm font-weight-bolder">Projets</span>
                enregistrés
              </p>
            </div>
          </div>
          <div class="col-4 text-end">
            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
              <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
    <div class="card card-futuristic">
      <div class="card-body p-3">
        <div class="row">
          <div class="col-8">
            <div class="numbers">
              <p class="text-sm mb-0 text-uppercase font-weight-bold">Projets en attente</p>
              <h5 class="font-weight-bolder"><?php echo $pendingProjects; ?></h5>
              <p class="mb-0">
                <span class="text-danger text-sm font-weight-bolder">Projets</span>
                à traiter
              </p>
            </div>
          </div>
          <div class="col-4 text-end">
            <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
              <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
    <div class="card card-futuristic">
      <div class="card-body p-3">
        <div class="row">
          <div class="col-8">
            <div class="numbers">
              <p class="text-sm mb-0 text-uppercase font-weight-bold">Projets approuvés</p>
              <h5 class="font-weight-bolder"><?php echo $approvedProjects; ?></h5>
              <p class="mb-0">
                <span class="text-success text-sm font-weight-bolder">Projets</span>
                validés
              </p>
            </div>
          </div>
          <div class="col-4 text-end">
            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
              <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="card card-futuristic">
      <div class="card-body p-3">
        <div class="row">
          <div class="col-8">
            <div class="numbers">
              <p class="text-sm mb-0 text-uppercase font-weight-bold">Catégories</p>
              <h5 class="font-weight-bolder"><?php echo $categoryCount; ?></h5>
              <p class="mb-0">
                <span class="text-success text-sm font-weight-bolder">Catégories</span>
                disponibles
              </p>
            </div>
          </div>
          <div class="col-4 text-end">
            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
              <i class="ni ni-tag text-lg opacity-10" aria-hidden="true"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Donut Charts Row -->
<div class="row mt-4">
  <div class="col-md-6 mb-4">
    <div class="card card-futuristic text-center p-4">
      <h6 class="mb-3">Répartition des Projets par Statut</h6>
      <div class="d-flex justify-content-center"><canvas id="donutStatusChart" width="180" height="180" style="max-width:180px;max-height:180px;"></canvas></div>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="card card-futuristic text-center p-4">
      <h6 class="mb-3">Projets par Catégorie</h6>
      <div class="d-flex justify-content-center"><canvas id="donutCategoryChart" width="180" height="180" style="max-width:180px;max-height:180px;"></canvas></div>
    </div>
  </div>
</div>

<!-- Recent projects table -->
<div class="row mt-4">
  <div class="col-12">
    <div class="card mb-4 card-futuristic">
      <div class="card-header pb-0">
        <div class="d-flex justify-content-between">
          <h6>Projets Récents</h6>
          <div>
            <a href="index.php?controller=project&action=export" class="btn btn-sm btn-danger me-2" title="Exporter en PDF">
              <i class="fas fa-file-pdf me-1"></i> Exporter PDF
            </a>
            <a href="index.php?controller=project&action=index" class="btn btn-sm btn-primary">Voir tous</a>
          </div>
        </div>
      </div>
      <div class="card-body px-0 pt-0 pb-2">
        <!-- Dynamic Search Input -->
        <div class="px-3 pt-3">
          <div class="input-group input-group-dynamic mb-4">
            <span class="input-group-text"><i class="fas fa-search" aria-hidden="true"></i></span>
            <input id="projectSearch" class="form-control" type="text" placeholder="Rechercher un projet (titre, catégorie, statut...)" style="padding-left: 10px;">
            <span class="search-results-count position-absolute end-0 top-0 mt-1 me-3 badge bg-gradient-primary rounded-pill" style="display: none;"></span>
          </div>
        </div>
        <div class="table-responsive p-0">
          <table id="projectsTable" class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 sortable" data-sort="title">
                  Projet <i class="fas fa-sort ms-1"></i>
                </th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2 sortable" data-sort="category">
                  Catégorie <i class="fas fa-sort ms-1"></i>
                </th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 sortable" data-sort="amount">
                  Montant <i class="fas fa-sort ms-1"></i>
                </th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 sortable" data-sort="status">
                  Statut <i class="fas fa-sort ms-1"></i>
                </th>
                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 sortable" data-sort="date">
                  Date <i class="fas fa-sort ms-1"></i>
                </th>
                <th class="text-secondary opacity-7"></th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($recentProjects)): ?>
              <tr class="no-results" style="display: none;">
                <td colspan="6" class="text-center py-4">
                  <div class="d-flex flex-column align-items-center">
                    <i class="ni ni-bullet-list-67 text-gradient-primary" style="font-size: 2.5rem;"></i>
                    <h6 class="mt-3">Aucun projet ne correspond à votre recherche</h6>
                  </div>
                </td>
              </tr>
              <?php endif; ?>
              <?php foreach($recentProjects as $project): ?>
              <tr class="search-row">
                <td>
                  <div class="d-flex px-2 py-1">
                    <div>
                      <img src="assets/img/small-logos/logo-xd.svg" class="avatar avatar-sm me-3" alt="project icon">
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                      <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($project['titre']); ?></h6>
                      <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($project['utilisateur_prenom']) . ' ' . htmlspecialchars($project['utilisateur_nom']); ?></p>
                    </div>
                  </div>
                </td>
                <td>
                  <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($project['categorie_nom']); ?></p>
                </td>
                <td class="align-middle text-center text-sm">
                  <span class="font-weight-bold"><?php echo number_format($project['montant_demande'], 2, ',', ' '); ?> €</span>
                </td>
                <td class="align-middle text-center">
                  <span class="badge badge-sm bg-gradient-<?php 
                    if($project['statut'] == 'Approuvé') {
                      echo 'success';
                    } elseif($project['statut'] == 'Refusé') {
                      echo 'danger';
                    } else {
                      echo 'warning';
                    }
                    ?>"><?php echo htmlspecialchars($project['statut']); ?></span>
                </td>
                <td class="align-middle text-center">
                  <span class="text-secondary text-xs font-weight-bold"><?php echo date("d/m/Y", strtotime($project['date_soumission'])); ?></span>
                </td>
                <td class="align-middle">
                  <a href="index.php?controller=project&action=show&id=<?php echo $project['id_projet']; ?>" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Voir le projet">
                    Détails
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Include footer
require_once 'views/admin/layout/footer.php';
?>

<script src="assets/js/plugins/chartjs.min.js"></script>
<script>
// Prepare data for status chart
const statusLabels = <?php echo json_encode(array_column($projectStatusCounts, 'statut')); ?>;
const statusData = <?php echo json_encode(array_column($projectStatusCounts, 'total')); ?>;
// Prepare data for category chart
const categoryLabels = <?php echo json_encode(array_column($projectCategoryCounts, 'category')); ?>;
const categoryData = <?php echo json_encode(array_column($projectCategoryCounts, 'total')); ?>;
// Template color palette
const templateColors = [
  '#5e72e4', '#11cdef', '#2dce89', '#fb6340', '#f5365c', '#ffd600', '#6c757d', '#172b4d', '#f4f5f7', '#8392ab'
];
// Donut Chart Options
const donutOptions = {
  cutout: '75%',
  borderRadius: 16,
  plugins: {
    legend: {
      display: true,
      labels: {
        color: '#5e72e4',
        font: { weight: 'bold', size: 14 }
      }
    }
  },
  animation: {
    animateRotate: true,
    animateScale: true
  }
};
// Status Donut Chart
new Chart(document.getElementById('donutStatusChart'), {
  type: 'doughnut',
  data: {
    labels: statusLabels,
    datasets: [{
      data: statusData,
      backgroundColor: templateColors,
      borderWidth: 3,
      borderColor: 'rgba(255,255,255,0.7)',
      hoverBorderColor: '#5e72e4',
      hoverOffset: 16
    }]
  },
  options: donutOptions
});
// Category Donut Chart
new Chart(document.getElementById('donutCategoryChart'), {
  type: 'doughnut',
  data: {
    labels: categoryLabels,
    datasets: [{
      data: categoryData,
      backgroundColor: templateColors,
      borderWidth: 3,
      borderColor: 'rgba(255,255,255,0.7)',
      hoverBorderColor: '#11cdef',
      hoverOffset: 16
    }]
  },
  options: donutOptions
});

// Project Search Functionality
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('projectSearch');
  const table = document.getElementById('projectsTable');
  const rows = table.querySelectorAll('tbody tr.search-row');
  const noResults = table.querySelector('.no-results');
  const resultsCount = document.querySelector('.search-results-count');
  
  // Add futuristic glowing effect on focus
  searchInput.addEventListener('focus', function() {
    this.parentElement.classList.add('input-group-focus');
    this.parentElement.style.boxShadow = '0 0 10px rgba(94, 114, 228, 0.5)';
  });
  
  searchInput.addEventListener('blur', function() {
    this.parentElement.classList.remove('input-group-focus');
    this.parentElement.style.boxShadow = 'none';
  });
  
  // Dynamic search functionality
  searchInput.addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase().trim();
    let visibleCount = 0;
    
    // Show/hide loading animation with delay for better UX
    if (searchTerm.length > 0) {
      // Add typing animation to search icon
      this.previousElementSibling.innerHTML = '<i class="fas fa-sync fa-spin" aria-hidden="true"></i>';
      setTimeout(() => {
        this.previousElementSibling.innerHTML = '<i class="fas fa-search" aria-hidden="true"></i>';
      }, 300);
    }
    
    // Filter rows based on search term
    rows.forEach(row => {
      const title = row.querySelector('h6').textContent.toLowerCase();
      const category = row.querySelector('.text-xs.font-weight-bold').textContent.toLowerCase();
      const status = row.querySelector('.badge').textContent.toLowerCase();
      const user = row.querySelector('.text-xs.text-secondary').textContent.toLowerCase();
      
      if (title.includes(searchTerm) || 
          category.includes(searchTerm) || 
          status.includes(searchTerm) || 
          user.includes(searchTerm)) {
        row.style.display = '';
        visibleCount++;
        
        // Highlight matching text
        if (searchTerm.length > 1) {
          highlightMatches(row, searchTerm);
        } else {
          removeHighlights(row);
        }
      } else {
        row.style.display = 'none';
        removeHighlights(row);
      }
    });
    
    // Display no results message if needed
    if (visibleCount === 0 && rows.length > 0) {
      if (noResults) noResults.style.display = 'table-row';
      resultsCount.style.display = 'none';
    } else {
      if (noResults) noResults.style.display = 'none';
      if (searchTerm.length > 0) {
        resultsCount.textContent = `${visibleCount} résultat${visibleCount > 1 ? 's' : ''}`;
        resultsCount.style.display = 'inline-block';
      } else {
        resultsCount.style.display = 'none';
      }
    }
  });
  
  // Highlight matching text
  function highlightMatches(row, term) {
    removeHighlights(row);
    
    const elements = [
      row.querySelector('h6'),
      row.querySelector('.text-xs.font-weight-bold'),
      row.querySelector('.badge')
    ];
    
    elements.forEach(el => {
      if (!el) return;
      const text = el.textContent;
      if (text.toLowerCase().includes(term)) {
        const regex = new RegExp(`(${term})`, 'gi');
        el.innerHTML = text.replace(regex, '<span class="highlight" style="background-color: rgba(94, 114, 228, 0.3); padding: 2px; border-radius: 3px;">$1</span>');
      }
    });
  }
  
  // Remove highlights
  function removeHighlights(row) {
    const highlights = row.querySelectorAll('.highlight');
    highlights.forEach(h => {
      const parent = h.parentNode;
      parent.textContent = parent.textContent;
    });
  }
  
  // Add clear button functionality
  const inputContainer = searchInput.parentElement;
  const clearButton = document.createElement('button');
  clearButton.className = 'btn btn-sm btn-link position-absolute end-0 text-xs';
  clearButton.innerHTML = '<i class="fas fa-times"></i>';
  clearButton.style.display = 'none';
  clearButton.style.top = '50%';
  clearButton.style.transform = 'translateY(-50%)';
  clearButton.style.right = '10px';
  clearButton.style.zIndex = '5';
  inputContainer.appendChild(clearButton);
  
  searchInput.addEventListener('input', function() {
    clearButton.style.display = this.value.length > 0 ? 'block' : 'none';
  });
  
  clearButton.addEventListener('click', function() {
    searchInput.value = '';
    searchInput.dispatchEvent(new Event('keyup'));
    this.style.display = 'none';
    searchInput.focus();
  });
  
  // Add smooth animations
  rows.forEach(row => {
    row.style.transition = 'background-color 0.3s ease, transform 0.2s ease';
    row.addEventListener('mouseenter', function() {
      this.style.backgroundColor = 'rgba(94, 114, 228, 0.05)';
      this.style.transform = 'translateY(-2px)';
    });
    row.addEventListener('mouseleave', function() {
      this.style.backgroundColor = '';
      this.style.transform = '';
    });
  });
});

// Table Sorting Functionality
const sortables = document.querySelectorAll('.sortable');
let currentSort = { column: null, direction: null };

// Add sort cursor and hover effect to sortable headers
sortables.forEach(sortable => {
  sortable.style.cursor = 'pointer';
  sortable.style.transition = 'color 0.3s ease, transform 0.2s ease';
  
  sortable.addEventListener('mouseenter', function() {
    this.style.color = '#5e72e4';
  });
  
  sortable.addEventListener('mouseleave', function() {
    if (currentSort.column !== this.dataset.sort) {
      this.style.color = '';
    }
  });
  
  // Add click event for sorting
  sortable.addEventListener('click', function() {
    const column = this.dataset.sort;
    let direction = 'asc';
    
    // Reset all sort icons
    sortables.forEach(s => {
      s.querySelector('i').className = 'fas fa-sort ms-1';
      s.style.color = '';
    });
    
    // Toggle direction if same column is clicked
    if (currentSort.column === column) {
      direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    }
    
    // Update sort icon
    this.querySelector('i').className = `fas fa-sort-${direction === 'asc' ? 'up' : 'down'} ms-1`;
    this.style.color = '#5e72e4';
    
    // Sort the table
    sortTable(column, direction);
    
    // Update current sort
    currentSort = { column, direction };
    
    // Add pulse animation to the icon
    const icon = this.querySelector('i');
    icon.style.animation = 'pulse 0.5s ease';
    setTimeout(() => {
      icon.style.animation = '';
    }, 500);
  });
});

// Sort table function
function sortTable(column, direction) {
  const table = document.getElementById('projectsTable');
  const tbody = table.querySelector('tbody');
  const rows = Array.from(tbody.querySelectorAll('tr.search-row'));
  
  // Sort rows based on column data
  const sortedRows = rows.sort((a, b) => {
    let aValue, bValue;
    
    if (column === 'title') {
      aValue = a.querySelector('h6').textContent.trim().toLowerCase();
      bValue = b.querySelector('h6').textContent.trim().toLowerCase();
    } 
    else if (column === 'category') {
      aValue = a.querySelector('.text-xs.font-weight-bold').textContent.trim().toLowerCase();
      bValue = b.querySelector('.text-xs.font-weight-bold').textContent.trim().toLowerCase();
    }
    else if (column === 'amount') {
      // Extract numeric value from amount
      aValue = parseFloat(a.querySelector('.font-weight-bold').textContent.replace(/[^\d,.]/g, '').replace(',', '.'));
      bValue = parseFloat(b.querySelector('.font-weight-bold').textContent.replace(/[^\d,.]/g, '').replace(',', '.'));
    }
    else if (column === 'status') {
      aValue = a.querySelector('.badge').textContent.trim().toLowerCase();
      bValue = b.querySelector('.badge').textContent.trim().toLowerCase();
    }
    else if (column === 'date') {
      // Convert date format DD/MM/YYYY to sortable format
      const aDate = a.querySelector('.text-secondary.text-xs').textContent.trim();
      const bDate = b.querySelector('.text-secondary.text-xs').textContent.trim();
      const [aDay, aMonth, aYear] = aDate.split('/');
      const [bDay, bMonth, bYear] = bDate.split('/');
      aValue = new Date(`${aYear}-${aMonth}-${aDay}`);
      bValue = new Date(`${bYear}-${bMonth}-${bDay}`);
    }
    
    // Compare values based on direction
    if (direction === 'asc') {
      return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
    } else {
      return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
    }
  });
  
  // Remove existing rows
  rows.forEach(row => row.remove());
  
  // Add sorted rows with animation
  sortedRows.forEach((row, index) => {
    row.style.opacity = '0';
    row.style.transform = 'translateY(10px)';
    tbody.appendChild(row);
    
    // Staggered animation for sorted rows
    setTimeout(() => {
      row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      row.style.opacity = '1';
      row.style.transform = 'translateY(0)';
    }, index * 50);
  });
  
  // Show sort feedback
  const tooltip = document.createElement('div');
  tooltip.classList.add('sort-tooltip');
  tooltip.textContent = `Trié par ${getColumnName(column)} (${direction === 'asc' ? '↑' : '↓'})`;
  tooltip.style.position = 'fixed';
  tooltip.style.bottom = '20px';
  tooltip.style.right = '20px';
  tooltip.style.padding = '10px 15px';
  tooltip.style.borderRadius = '30px';
  tooltip.style.backgroundColor = 'rgba(94, 114, 228, 0.9)';
  tooltip.style.color = 'white';
  tooltip.style.boxShadow = '0 5px 15px rgba(94, 114, 228, 0.3)';
  tooltip.style.zIndex = '1000';
  tooltip.style.opacity = '0';
  tooltip.style.transform = 'translateY(20px)';
  tooltip.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
  
  document.body.appendChild(tooltip);
  
  setTimeout(() => {
    tooltip.style.opacity = '1';
    tooltip.style.transform = 'translateY(0)';
  }, 100);
  
  setTimeout(() => {
    tooltip.style.opacity = '0';
    tooltip.style.transform = 'translateY(20px)';
    setTimeout(() => {
      tooltip.remove();
    }, 300);
  }, 2000);
}

// Helper function to get column name for tooltip
function getColumnName(column) {
  switch(column) {
    case 'title': return 'Projet';
    case 'category': return 'Catégorie';
    case 'amount': return 'Montant';
    case 'status': return 'Statut';
    case 'date': return 'Date';
    default: return column;
  }
}

// Add keyframes for pulse animation
const style = document.createElement('style');
style.textContent = `
  @keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
  }
`;
document.head.appendChild(style);
</script> 