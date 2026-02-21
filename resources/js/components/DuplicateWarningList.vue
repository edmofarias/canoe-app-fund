<template>
  <div class="duplicate-warning-list">
    <h2>Duplicate Fund Warnings</h2>
    
    <div v-if="loading" class="loading">Loading warnings...</div>
    
    <div v-else-if="error" class="error">{{ error }}</div>
    
    <div v-else>
      <div v-if="warnings.length === 0" class="empty-state">
        No unresolved duplicate warnings found.
      </div>
      
      <div v-else class="warnings-container">
        <div v-for="warning in warnings" :key="warning.id" class="warning-card">
          <div class="warning-header">
            <span class="warning-badge">Duplicate Detected</span>
            <span class="warning-date">{{ formatDate(warning.created_at) }}</span>
          </div>
          
          <div class="comparison-container">
            <!-- Fund 1 -->
            <div class="fund-comparison">
              <h3>Fund 1</h3>
              <div class="fund-info">
                <p><strong>Name:</strong> {{ warning.fund1?.name || 'N/A' }}</p>
                <p><strong>Start Year:</strong> {{ warning.fund1?.start_year || 'N/A' }}</p>
                <p><strong>Manager:</strong> {{ warning.fund1?.fund_manager?.name || 'N/A' }}</p>
                
                <div v-if="warning.fund1?.aliases && warning.fund1.aliases.length > 0" class="fund-aliases">
                  <strong>Aliases:</strong>
                  <span v-for="(alias, index) in warning.fund1.aliases" :key="alias.id">
                    {{ alias.name }}<span v-if="index < warning.fund1.aliases.length - 1">, </span>
                  </span>
                </div>
                
                <div v-if="warning.fund1?.companies && warning.fund1.companies.length > 0" class="fund-companies">
                  <strong>Companies:</strong>
                  <span v-for="(company, index) in warning.fund1.companies" :key="company.id">
                    {{ company.name }}<span v-if="index < warning.fund1.companies.length - 1">, </span>
                  </span>
                </div>
              </div>
            </div>
            
            <div class="comparison-divider">
              <span class="vs-badge">VS</span>
            </div>
            
            <!-- Fund 2 -->
            <div class="fund-comparison">
              <h3>Fund 2</h3>
              <div class="fund-info">
                <p><strong>Name:</strong> {{ warning.fund2?.name || 'N/A' }}</p>
                <p><strong>Start Year:</strong> {{ warning.fund2?.start_year || 'N/A' }}</p>
                <p><strong>Manager:</strong> {{ warning.fund2?.fund_manager?.name || 'N/A' }}</p>
                
                <div v-if="warning.fund2?.aliases && warning.fund2.aliases.length > 0" class="fund-aliases">
                  <strong>Aliases:</strong>
                  <span v-for="(alias, index) in warning.fund2.aliases" :key="alias.id">
                    {{ alias.name }}<span v-if="index < warning.fund2.aliases.length - 1">, </span>
                  </span>
                </div>
                
                <div v-if="warning.fund2?.companies && warning.fund2.companies.length > 0" class="fund-companies">
                  <strong>Companies:</strong>
                  <span v-for="(company, index) in warning.fund2.companies" :key="company.id">
                    {{ company.name }}<span v-if="index < warning.fund2.companies.length - 1">, </span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { duplicateWarningApi } from '../api.js';

export default {
  name: 'DuplicateWarningList',
  data() {
    return {
      warnings: [],
      loading: false,
      error: null
    };
  },
  mounted() {
    this.fetchWarnings();
  },
  methods: {
    async fetchWarnings() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await duplicateWarningApi.list();
        this.warnings = response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load duplicate warnings';
        console.error('Error fetching warnings:', err);
      } finally {
        this.loading = false;
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      
      const date = new Date(dateString);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);
      
      if (diffMins < 1) return 'Just now';
      if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
      if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
      if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
      
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
}
</script>

<style scoped>
.duplicate-warning-list {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.duplicate-warning-list h2 {
  margin-top: 0;
  color: #333;
  margin-bottom: 1.5rem;
}

.loading, .error, .empty-state {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.error {
  color: #d32f2f;
}

.warnings-container {
  margin-top: 1rem;
}

.warning-card {
  border: 2px solid #ff9800;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  background-color: #fff3e0;
}

.warning-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.warning-badge {
  background-color: #ff9800;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-weight: bold;
  font-size: 0.9rem;
}

.warning-date {
  color: #666;
  font-size: 0.9rem;
}

.comparison-container {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  gap: 1.5rem;
  align-items: start;
}

.fund-comparison {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 1rem;
}

.fund-comparison h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #1976d2;
  font-size: 1.1rem;
}

.fund-info p {
  margin: 0.5rem 0;
  color: #555;
}

.fund-aliases, .fund-companies {
  margin-top: 0.75rem;
  color: #555;
}

.comparison-divider {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-top: 2rem;
}

.vs-badge {
  background-color: #d32f2f;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 50%;
  font-weight: bold;
  font-size: 0.9rem;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 768px) {
  .comparison-container {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .comparison-divider {
    padding-top: 0;
    padding-bottom: 0;
  }
  
  .vs-badge {
    width: 40px;
    height: 40px;
    font-size: 0.8rem;
  }
}
</style>
