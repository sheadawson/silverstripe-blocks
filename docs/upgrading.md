# Upgrading

## Upgrading from 0.x

1. Upgrade your module to the latest code, run dev/build
2. The database structure of Blocks 1.0 differs slightly from earier versions, so backup your database, cross your fingers and run dev/tasks/BlockUpgradeTask. This will adapt your current Block records to the new structure. See BlockUpgradeTask.php for exact details.
3. Check your blocks to make sure they're all happy.
