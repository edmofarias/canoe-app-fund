<template>
  <div class="fund-list-view">
    <div class="header">
      <h1>Funds</h1>
      <router-link to="/funds/create" class="btn-create">Create New Fund</router-link>
    </div>
    
    <FilterBar @filter-change="handleFilterChange" />
    <FundList :filters="filters" />
  </div>
</template>

<script>
import FilterBar from '../components/FilterBar.vue';
import FundList from '../components/FundList.vue';

export default {
  name: 'FundListView',
  components: {
    FilterBar,
    FundList
  },
  data() {
    return {
      filters: {}
    };
  },
  methods: {
    handleFilterChange(newFilters) {
      this.filters = newFilters;
    }
  }
}
</script>

<style scoped>
.fund-list-view {
  width: 100%;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.header h1 {
  color: #333;
  margin: 0;
}

.btn-create {
  padding: 0.75rem 1.5rem;
  background-color: #4caf50;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.btn-create:hover {
  background-color: #45a049;
}
</style>
