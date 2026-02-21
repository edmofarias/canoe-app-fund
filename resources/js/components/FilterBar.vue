<template>
  <div class="filter-bar">
    <h3>Filter Funds</h3>
    
    <div class="filters">
      <div class="filter-group">
        <label for="filter-name">Fund Name</label>
        <input
          id="filter-name"
          v-model="localFilters.name"
          type="text"
          placeholder="Search by name..."
          @input="emitFilters"
        />
      </div>
      
      <div class="filter-group">
        <label for="filter-manager">Fund Manager</label>
        <select
          id="filter-manager"
          v-model="localFilters.fund_manager_id"
          @change="emitFilters"
        >
          <option value="">All Managers</option>
          <option v-for="manager in fundManagers" :key="manager.id" :value="manager.id">
            {{ manager.name }}
          </option>
        </select>
      </div>
      
      <div class="filter-group">
        <label for="filter-year">Start Year</label>
        <input
          id="filter-year"
          v-model="localFilters.start_year"
          type="number"
          placeholder="e.g., 2020"
          @input="emitFilters"
        />
      </div>
      
      <div class="filter-group">
        <label for="filter-company">Company</label>
        <select
          id="filter-company"
          v-model="localFilters.company_id"
          @change="emitFilters"
        >
          <option value="">All Companies</option>
          <option v-for="company in companies" :key="company.id" :value="company.id">
            {{ company.name }}
          </option>
        </select>
      </div>
      
      <div class="filter-actions">
        <button @click="clearFilters" class="btn-clear">Clear Filters</button>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../api.js';

export default {
  name: 'FilterBar',
  emits: ['filter-change'],
  data() {
    return {
      localFilters: {
        name: '',
        fund_manager_id: '',
        start_year: '',
        company_id: ''
      },
      fundManagers: [],
      companies: []
    };
  },
  mounted() {
    this.fetchFundManagers();
    this.fetchCompanies();
  },
  methods: {
    async fetchFundManagers() {
      try {
        const response = await api.get('/fund-managers');
        this.fundManagers = response.data;
      } catch (err) {
        console.error('Error fetching fund managers:', err);
      }
    },
    
    async fetchCompanies() {
      try {
        const response = await api.get('/companies');
        this.companies = response.data;
      } catch (err) {
        console.error('Error fetching companies:', err);
      }
    },
    
    emitFilters() {
      // Only emit non-empty filters
      const filters = {};
      
      if (this.localFilters.name) {
        filters.name = this.localFilters.name;
      }
      if (this.localFilters.fund_manager_id) {
        filters.fund_manager_id = this.localFilters.fund_manager_id;
      }
      if (this.localFilters.start_year) {
        filters.start_year = this.localFilters.start_year;
      }
      if (this.localFilters.company_id) {
        filters.company_id = this.localFilters.company_id;
      }
      
      this.$emit('filter-change', filters);
    },
    
    clearFilters() {
      this.localFilters = {
        name: '',
        fund_manager_id: '',
        start_year: '',
        company_id: ''
      };
      this.emitFilters();
    }
  }
}
</script>

<style scoped>
.filter-bar {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 1.5rem;
}

.filter-bar h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #333;
}

.filters {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  align-items: end;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-group label {
  margin-bottom: 0.5rem;
  color: #555;
  font-weight: 500;
  font-size: 0.9rem;
}

.filter-group input,
.filter-group select {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.filter-group input:focus,
.filter-group select:focus {
  outline: none;
  border-color: #1976d2;
}

.filter-actions {
  display: flex;
  align-items: flex-end;
}

.btn-clear {
  padding: 0.5rem 1rem;
  background-color: #757575;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.btn-clear:hover {
  background-color: #616161;
}
</style>
