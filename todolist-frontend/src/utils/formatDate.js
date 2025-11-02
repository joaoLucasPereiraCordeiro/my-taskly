export function parseDateBR(dateStr) {
  if (!dateStr) return null;
  const parts = dateStr.split("/");
  if (parts.length === 3) {
    const [day, month, year] = parts;
    return new Date(+year, +month - 1, +day);
  }
  return new Date(dateStr);
}

export function formatDateBR(date) {
  if (!date) return "";
  if (typeof date === "string" && date.includes("/")) return date;
  const d = new Date(date);
  if (isNaN(d)) return "";
  return d.toLocaleDateString("pt-BR");
}

export function formatDateToISO(dateStr) {
  if (!dateStr) return null;

  if (dateStr instanceof Date) {
    return dateStr.toISOString().split("T")[0];
  }

  const parts = dateStr.split("/");
  if (parts.length === 3) {
    return `${parts[2]}-${parts[1].padStart(2, "0")}-${parts[0].padStart(
      2,
      "0"
    )}`;
  }

  return dateStr;
}
