<template>
  <div class="fund-list">
    <div v-if="loading" class="loading">Loading funds...</div>
    
    <div v-else-if="error" class="error">{{ error }}</div>
    
    <div v-else>
      <div v-if="funds.length === 0" class="empty-state">
        No funds found.
      </div>
      
      <div v-else class="funds-container">
        <div v-for="fund in funds" :key="fund.id" class="fund-card">
          <div class="fund-header">
            <h3>{{ fund.name }}</h3>
            <div class="fund-actions">
              <button @click="editFund(fund.id)" class="btn-edit">Edit</button>
              <button @click="confirmDelete(fund)" class="btn-delete">Delete</button>
            </div>
          </div>
          
          <div class="fund-details">
            <p><strong>Start Year:</strong> {{ fund.start_year }}</p>
            <p><strong>Fund Manager:</strong> {{ fund.fund_manager?.name || 'N/A' }}</p>
            
            <div v-if="fund.aliases && fund.aliases.length > 0" class="fund-aliases">
              <strong>Aliases:</strong>
              <span v-for="(alias, index) in fund.aliases" :key="alias.id">
                {{ alias.name }}<span v-if="index < fund.aliases.length - 1">, </span>
              </span>
            </div>
            
            <div v-if="fund.companies && fund.companies.length > 0" class="fund-companies">
              <strong>Companies:</strong>
              <span v-for="(company, index) in fund.companies" :key="company.id">
                {{ company.name }}<span v-if="index < fund.companies.length - 1">, </span>
              </span>
            </div>
          </div>
        </div>
        
        <div v-if="pagination.total > pagination.per_page" class="pagination">
          <button 
            @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page === 1"
            class="btn-page"
          >
            Previous
          </button>
          <span class="page-info">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>
          <button 
            @click="changePage(pagination.current_page + 1)" 
            :disabled="pagination.current_page === pagination.last_page"
            class="btn-page"
          >
            Next
          </button>
        </div>
      </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="cancelDelete">
      <div class="modal" @click.stop>
        <h3>Confirm Delete</h3>
        <p>Are you sure you want to delete "{{ fundToDelete?.name }}"?</p>
        <div class="modal-actions">
          <button @click="cancelDelete" class="btn-cancel">Cancel</button>
          <button @click="deleteFund" class="btn-confirm-delete">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { fundApi } from '../api.js';

export default {
  name: 'FundList',
  props: {
    filters: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      funds: [],
      loading: false,
      error: null,
      showDeleteModal: false,
      fundToDelete: null,
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
      }
    };
  },
  watch: {
    filters: {
      handler() {
        this.pagination.current_page = 1;
        this.fetchFunds();
      },
      deep: true
    }
  },
  mounted() {
    this.fetchFunds();
  },
  methods: {
    async fetchFunds() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = {
          page: this.pagination.current_page,
          ...this.filters
        };
        
        const response = await fundApi.list(params);
        
        if (response.data.data) {
          // Laravel pagination format
          this.funds = response.data.data;
          this.pagination = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total
          };
        } else {
          // Simple array format
          this.funds = response.data;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load funds';
        console.error('Error fetching funds:', err);
      } finally {
        this.loading = false;
      }
    },
    
    editFund(id) {
      this.$router.push({ name: 'fund-edit', params: { id } });
    },
    
    confirmDelete(fund) {
      this.fundToDelete = fund;
      this.showDeleteModal = true;
    },
    
    cancelDelete() {
      this.showDeleteModal = false;
      this.fundToDelete = null;
    },
    
    async deleteFund() {
      if (!this.fundToDelete) return;
      
      try {
        await fundApi.delete(this.fundToDelete.id);
        this.showDeleteModal = false;
        this.fundToDelete = null;
        await this.fetchFunds();
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete fund';
        console.error('Error deleting fund:', err);
        this.showDeleteModal = false;
      }
    },
    
    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.pagination.current_page = page;
        this.fetchFunds();
      }
    }
  }
}
</script>

<style scoped>
.fund-list {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.loading, .error, .empty-state {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.error {
  color: #d32f2f;
}

.funds-container {
  margin-top: 1rem;
}

.fund-card {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.fund-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.fund-header h3 {
  margin: 0;
  color: #333;
}

.fund-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-edit, .btn-delete, .btn-page, .btn-cancel, .btn-confirm-delete {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.btn-edit {
  background-color: #1976d2;
  color: white;
}

.btn-edit:hover {
  background-color: #1565c0;
}

.btn-delete {
  background-color: #d32f2f;
  color: white;
}

.btn-delete:hover {
  background-color: #c62828;
}

.fund-details {
  color: #555;
}

.fund-details p {
  margin: 0.5rem 0;
}

.fund-aliases, .fund-companies {
  margin-top: 0.5rem;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-page {
  background-color: #1976d2;
  color: white;
}

.btn-page:hover:not(:disabled) {
  background-color: #1565c0;
}

.btn-page:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.page-info {
  color: #666;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 400px;
  width: 90%;
}

.modal h3 {
  margin-top: 0;
  color: #333;
}

.modal p {
  color: #666;
  margin: 1rem 0;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1.5rem;
}

.btn-cancel {
  background-color: #757575;
  color: white;
}

.btn-cancel:hover {
  background-color: #616161;
}

.btn-confirm-delete {
  background-color: #d32f2f;
  color: white;
}

.btn-confirm-delete:hover {
  background-color: #c62828;
}
</style>
