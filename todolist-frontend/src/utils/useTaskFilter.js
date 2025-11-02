export function filterTasks(tasks, activeFilter) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const parseDate = (dateStr) => {
    const parts = dateStr.split("/");
    if (parts.length === 3) {
      const [day, month, year] = parts;
      return new Date(+year, +month - 1, +day);
    }
    return new Date(dateStr);
  };

  return tasks.filter((task) => {
    if (!task.due_date) return false;

    const dueDate = parseDate(task.due_date);
    if (isNaN(dueDate)) return false;

    dueDate.setHours(0, 0, 0, 0);

    switch (activeFilter) {
      case "all":
        return true;
      case "completed":
        return task.completed;
      case "pending":
        return !task.completed && dueDate < today;
      case "today":
        return !task.completed && dueDate.getTime() === today.getTime();
      case "week": {
        const startOfWeek = new Date(today);
        startOfWeek.setDate(today.getDate() - today.getDay());
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        return (
          !task.completed && dueDate >= startOfWeek && dueDate <= endOfWeek
        );
      }
      case "month":
        return (
          !task.completed &&
          dueDate.getMonth() === today.getMonth() &&
          dueDate.getFullYear() === today.getFullYear()
        );
      default:
        return true;
    }
  });
}
