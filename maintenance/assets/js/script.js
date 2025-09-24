document.addEventListener("DOMContentLoaded", function () {
  const reportCards = document.querySelectorAll(".report-card");

  reportCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.classList.add("hover");
    });

    card.addEventListener("mouseleave", function () {
      this.classList.remove("hover");
    });
  });

  let zIndexCounter = 1000;

  function openModal(modal) {
    if (!modal) return;
    zIndexCounter++;
    modal.style.zIndex = zIndexCounter;
    modal.classList.add("active");
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove("active");
  }

  const hamburgerMenu = document.getElementById("hamburger-menu");
  const sidebarStateKey = "sidebarState";

  if (hamburgerMenu) {
    hamburgerMenu.addEventListener("click", () => {
      document.documentElement.classList.toggle("sidebar-collapsed");
      if (document.documentElement.classList.contains("sidebar-collapsed")) {
        localStorage.setItem(sidebarStateKey, "collapsed");
      } else {
        localStorage.setItem(sidebarStateKey, "expanded");
      }
    });
  }

  function updateDateTime() {
    const dateContainer = document.getElementById("header-date-container");
    const timeContainer = document.getElementById("header-time-container");
    const now = new Date();

    if (dateContainer) {
      const dateOptions = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
      };
      dateContainer.textContent = now.toLocaleDateString("id-ID", dateOptions);
    }

    if (timeContainer) {
      const timeOptions = {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
      };
      timeContainer.textContent = now.toLocaleTimeString("id-ID", timeOptions);
    }
  }

  updateDateTime();
  setInterval(updateDateTime, 1000);

  const themeSwitcher = document.getElementById("themeSwitcher");
  if (themeSwitcher) {
    if (localStorage.getItem("theme") === "dark") {
      themeSwitcher.checked = true;
    }
    themeSwitcher.addEventListener("change", function () {
      if (this.checked) {
        document.documentElement.classList.add("dark-mode");
        localStorage.setItem("theme", "dark");
      } else {
        document.documentElement.classList.remove("dark-mode");
        localStorage.setItem("theme", "light");
      }
    });
  }

  const quickActionsBtn = document.getElementById("quickActionsBtn");
  if (quickActionsBtn) {
    quickActionsBtn.addEventListener("click", function (event) {
      event.stopPropagation();
      document.querySelectorAll(".dropdown-menu.show").forEach((menu) => {
        if (menu.id !== "quickActionsMenu") {
          menu.classList.remove("show");
        }
      });
      document.getElementById("quickActionsMenu").classList.toggle("show");
    });
  }

  const quickAddUpsBtn = document.getElementById("quickAddUpsBtn");
  if (quickAddUpsBtn) {
    quickAddUpsBtn.addEventListener("click", function (e) {
      if (window.location.pathname.includes("/ups/list")) {
        e.preventDefault();
        openModal(document.getElementById("addUpsModal"));
        document.getElementById("quickActionsMenu").classList.remove("show");
      }
    });
  }

  if (
    window.location.hash === "#add" &&
    window.location.pathname.includes("/ups/list")
  ) {
    openModal(document.getElementById("addUpsModal"));
  }

  const globalSearchInput = document.getElementById("globalSearchInput");
  if (globalSearchInput) {
    globalSearchInput.addEventListener("keyup", function (e) {
      const searchTerm = e.target.value;
      const pageSearchInput = document.querySelector(
        "#searchInput, #historySearchInput, #dashboardSearchInput"
      );
      if (pageSearchInput) {
        pageSearchInput.value = searchTerm;
        pageSearchInput.dispatchEvent(new Event("keyup"));
      }
    });
  }

  const chartVisibilityToggleBtn = document.getElementById(
    "chartVisibilityToggleBtn"
  );
  const chartVisibilityKey = "chartVisibilityState";
  if (chartVisibilityToggleBtn) {
    const icon = chartVisibilityToggleBtn.querySelector("i");
    const updateViewState = (state) => {
      if (state === "hidden") {
        document.documentElement.classList.add("charts-hidden");
        icon.classList.replace("fa-eye", "fa-eye-low-vision");
        localStorage.setItem(chartVisibilityKey, "hidden");
      } else {
        document.documentElement.classList.remove("charts-hidden");
        icon.classList.replace("fa-eye-low-vision", "fa-eye");
        localStorage.setItem(chartVisibilityKey, "shown");
      }
    };
    if (localStorage.getItem(chartVisibilityKey) === "hidden") {
      icon.classList.replace("fa-eye", "fa-eye-low-vision");
    }
    chartVisibilityToggleBtn.addEventListener("click", () => {
      const isHidden =
        document.documentElement.classList.contains("charts-hidden");
      updateViewState(isHidden ? "shown" : "hidden");
    });
  }

  function setupCustomSelect(wrapper) {
    const trigger = wrapper.querySelector(".custom-select-trigger");
    const optionsContainer = wrapper.querySelector(".custom-options");
    const searchInput = wrapper.querySelector(".custom-select-search");
    const optionsList = wrapper.querySelector(".options-list");
    const hiddenSelect = wrapper.querySelector("select");
    if (!trigger || !optionsContainer || !optionsList || !hiddenSelect) return;
    const hasSearch = searchInput !== null;

    optionsList.innerHTML = "";
    Array.from(hiddenSelect.options).forEach((option) => {
      if (option.value === "" && hiddenSelect.required) return;
      const customOption = document.createElement("div");
      customOption.className = "custom-option";
      customOption.textContent = option.textContent;
      customOption.dataset.value = option.value;
      for (const attr of option.attributes) {
        if (attr.name.startsWith("data-")) {
          customOption.setAttribute(attr.name, attr.value);
        }
      }
      if (option.selected) {
        customOption.classList.add("selected");
      }
      optionsList.appendChild(customOption);
    });
    const customOptions = optionsList.querySelectorAll(".custom-option");

    trigger.addEventListener("click", (e) => {
      e.stopPropagation();
      const wasOpen = wrapper.classList.contains("open");
      document
        .querySelectorAll(".custom-select-wrapper.open")
        .forEach((w) => w.classList.remove("open"));
      if (!wasOpen) {
        wrapper.classList.add("open");
        if (hasSearch) searchInput.focus();
      }
    });

    optionsContainer.addEventListener("click", (e) => e.stopPropagation());

    if (hasSearch) {
      searchInput.addEventListener("keyup", () => {
        const filter = searchInput.value.toUpperCase();
        customOptions.forEach((opt) =>
          opt.classList.toggle(
            "d-none",
            !opt.textContent.toUpperCase().includes(filter)
          )
        );
      });
      searchInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          const firstVisibleOption = optionsList.querySelector(
            ".custom-option:not(.d-none)"
          );
          if (firstVisibleOption) firstVisibleOption.click();
        }
      });
    }

    optionsList.addEventListener("click", (e) => {
      const option = e.target.closest(".custom-option");
      if (option) {
        if (hiddenSelect.value !== option.dataset.value) {
          hiddenSelect.value = option.dataset.value;
          hiddenSelect.dispatchEvent(new Event("change", { bubbles: true }));
        }
        const triggerSpan = trigger.querySelector("span");
        triggerSpan.textContent = option.textContent;
        triggerSpan.classList.remove("placeholder");
        customOptions.forEach((opt) => opt.classList.remove("selected"));
        option.classList.add("selected");
        wrapper.classList.remove("open");
      }
    });
  }

  function updateView(container) {
    const viewTableRadio = container.querySelector("#view-table");
    const viewCardRadio = container.querySelector("#view-card");
    const tableContainer = container.querySelector(".table-container");
    const cardContainer = container.querySelector(".report-cards-wrapper");
    if (viewTableRadio && viewCardRadio && tableContainer && cardContainer) {
      if (viewTableRadio.checked) {
        tableContainer.classList.remove("view-hidden");
        cardContainer.classList.add("view-hidden");
      } else if (viewCardRadio.checked) {
        cardContainer.classList.remove("view-hidden");
        tableContainer.classList.add("view-hidden");
      }
    }
  }

  // REVISED: This function now initializes all controls that might be dynamically reloaded.
  function initializeDynamicControls(container) {
    // Setup search bars within the given container
    const searchInputs = {
      "#searchInput": "#upsTable tbody tr",
      "#historySearchInput": "#historyTable tbody tr",
      "#dashboardSearchInput": "#reportsTable tbody tr, .report-card",
    };
    for (const [id, selector] of Object.entries(searchInputs)) {
      const searchInput = container.querySelector(id);
      if (searchInput) {
        searchInput.addEventListener("keyup", function () {
          const filter = searchInput.value.toUpperCase();
          container.querySelectorAll(selector).forEach((item) => {
            item.style.display =
              (item.textContent || item.innerText)
                .toUpperCase()
                .indexOf(filter) > -1
                ? ""
                : "none";
          });
        });
      }
    }

    // Setup custom select dropdowns
    container
      .querySelectorAll(".custom-select-wrapper")
      .forEach(setupCustomSelect);

    // Setup file inputs
    container.querySelectorAll(".attachment-box").forEach((attachmentBox) => {
      const fileInput =
        attachmentBox.nextElementSibling ||
        document.getElementById(attachmentBox.getAttribute("for"));
      const textElement = attachmentBox.querySelector(".attachment-text");
      if (fileInput && textElement) {
        fileInput.addEventListener("change", function () {
          textElement.textContent =
            this.files.length > 0
              ? this.files[0].name
              : attachmentBox.dataset.defaultText ||
                "Unggah file atau seret & jatuhkan";
        });
        attachmentBox.addEventListener("dragover", (e) => {
          e.preventDefault();
          attachmentBox.classList.add("dragover");
        });
        attachmentBox.addEventListener("dragleave", () => {
          attachmentBox.classList.remove("dragover");
        });
        attachmentBox.addEventListener("drop", (e) => {
          e.preventDefault();
          attachmentBox.classList.remove("dragover");
          if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event("change"));
          }
        });
      }
    });

    // Setup view switcher for reports
    const viewTableRadio = container.querySelector("#view-table");
    const viewCardRadio = container.querySelector("#view-card");
    if (viewTableRadio && viewCardRadio) {
      viewTableRadio.addEventListener("change", () => updateView(container));
      viewCardRadio.addEventListener("change", () => updateView(container));
    }
    // Also run updateView once to set the initial state correctly
    updateView(container);
  }

  // Initialize all controls on the initial page load
  initializeDynamicControls(document);

  window.addEventListener("click", () => {
    document
      .querySelectorAll(".custom-select-wrapper.open")
      .forEach((w) => w.classList.remove("open"));
    document
      .querySelectorAll(".dropdown-menu.show")
      .forEach((m) => m.classList.remove("show"));
  });

  const exportDropdownBtn = document.getElementById("exportDropdownBtn");
  if (exportDropdownBtn) {
    exportDropdownBtn.addEventListener("click", (event) => {
      event.stopPropagation();
      document.getElementById("exportDropdownMenu").classList.toggle("show");
    });
  }

  const spinner = document.getElementById("loading-spinner");

  async function fetchExportData() {
    if (spinner) spinner.style.display = "flex";
    const params = new URLSearchParams(window.location.search);
    params.delete("page");
    try {
      const response = await fetch(
        `${BASE_URL}/views/hasil/fetch_all_reports?${params.toString()}`
      );
      if (!response.ok) throw new Error("Network response was not ok");
      return await response.json();
    } catch (error) {
      console.error("Gagal mengambil data ekspor:", error);
      showNotification("Gagal mengambil data untuk ekspor.", "error");
      return null;
    } finally {
      if (spinner) spinner.style.display = "none";
    }
  }

  function escapeCsvCell(cellData) {
    if (cellData === null || cellData === undefined) return "";
    let cell = String(cellData);
    if (cell.search(/(,|"|\n)/g) >= 0) {
      cell = `"${cell.replace(/"/g, '""')}"`;
    }
    return cell;
  }

  const exportExcelBtn = document.getElementById("exportExcelBtn");
  if (exportExcelBtn) {
    exportExcelBtn.addEventListener("click", async () => {
      const data = await fetchExportData();
      if (data && data.length > 0) {
        const formatDate = (dateString) => {
          if (!dateString || dateString === "0000-00-00 00:00:00") return "";
          return new Date(dateString).toLocaleString("id-ID");
        };
        const headers = [
          "No. Laporan",
          "Nama UPS",
          "Lokasi",
          "Merk",
          "Tipe",
          "Kapasitas (KVA)",
          "Jumlah Baterai",
          "Alamat IP",
          "Departemen",
          "Tanggal & Waktu Check-in",
          "Tanggal & waktu Check-out",
          "Status",
          "Jenis Pemeliharaan",
          "Teknisi",
          "Hasil Pengecekan",
          "Tindakan yang diambil",
        ];
        let csvContent = headers.join(";") + "\r\n";
        data.forEach((row) => {
          const rowData = [
            `RL-${String(row.maintenance_id).padStart(4, "0")}`,
            row.nama_ups,
            row.lokasi,
            row.merk,
            row.tipe_ups,
            `${row.ukuran_kapasitas} KVA`,
            row.jumlah_baterai,
            row.ip_address,
            row.perusahaan_maintenance,
            formatDate(row.tanggal_jadwal),
            formatDate(row.tanggal_pelaksanaan),
            (row.status || "") + (row.subStatus ? `${row.subStatus}` : ""),
            (row.jenis || "") + (row.sub_jenis ? ` ${row.sub_jenis}` : ""),
            row.teknisi,
            row.hasil_pengecekan,
            row.pengubahan,
          ];
          csvContent += rowData.map(escapeCsvCell).join(";") + "\r\n";
        });
        const blob = new Blob(["\uFEFF" + csvContent], {
          type: "text/csv;charset=utf-8;",
        });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "Riwayat-Pemeliharaan.csv";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      } else if (data) {
        showNotification("Tidak ada data untuk diekspor.", "warning");
      }
    });
  }

  const exportPdfBtn = document.getElementById("exportPdfBtn");
  if (exportPdfBtn) {
    exportPdfBtn.addEventListener("click", async () => {
      const data = await fetchExportData();
      if (data && data.length > 0) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: "landscape" });
        const tableColumn = [
          "No. Laporan",
          "Lokasi",
          "Kapasitas",
          "Departemen",
          "Teknisi",
          "Tanggal Pelaksanaan",
          "Jenis",
          "Status",
        ];
        const tableRows = data.map((item) => [
          `RL-${String(item.maintenance_id).padStart(4, "0")}`,
          item.lokasi || "N/A",
          item.ukuran_kapasitas ? `${item.ukuran_kapasitas} KVA` : "N/A",
          item.perusahaan_maintenance || "N/A",
          item.teknisi || "N/A",
          item.tanggal_pelaksanaan
            ? new Date(item.tanggal_pelaksanaan).toLocaleString("id-ID", {
                day: "numeric",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
              })
            : "N/A",
          (item.jenis || "N/A") + (item.sub_jenis ? `\n${item.sub_jenis}` : ""),
          (item.status || "N/A") +
            (item.subStatus ? `\n${item.subStatus}` : ""),
        ]);
        doc.autoTable(tableColumn, tableRows, {
          startY: 20,
          headStyles: { halign: "center", valign: "middle" },
          styles: { halign: "center", valign: "middle" },
        });
        doc.text("Laporan Riwayat Pemeliharaan", 14, 15);
        doc.save("Riwayat-Pemeliharaan.pdf");
      } else if (data) {
        showNotification("Tidak ada data untuk diekspor.", "warning");
      }
    });
  }

  window.showNotification = function (message, type = "success") {
    const container = document.getElementById("notification-container");
    if (!container) return;
    const icons = {
      success: '<i class="fas fa-check-circle"></i>',
      error: '<i class="fas fa-times-circle"></i>',
      warning: '<i class="fas fa-exclamation-triangle"></i>',
    };
    const titles = {
      success: "Berhasil!",
      error: "Gagal!",
      warning: "Peringatan!",
    };
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `<div class="notification-body"><div class="notification-icon">${
      icons[type] || ""
    }</div><div class="notification-content"><h4>${
      titles[type] || "Notifikasi"
    }</h4><p>${message}</p></div><button class="notification-close" type="button">&times;</button></div><div class="notification-progress"></div>`;
    container.appendChild(notification);
    setTimeout(() => {
      notification.style.transform = "translateX(0)";
      notification.style.opacity = "1";
    }, 10);
    const progress = notification.querySelector(".notification-progress");
    progress.style.animation = "progress-bar-animation 5s linear forwards";
    const close = () => {
      notification.style.transform = "translateX(120%)";
      notification.style.opacity = "0";
      setTimeout(() => notification.remove(), 300);
    };
    notification
      .querySelector(".notification-close")
      .addEventListener("click", close);
    setTimeout(close, 5000);
  };

  const notificationDataElement = document.getElementById("notification-data");
  if (notificationDataElement) {
    try {
      const notificationData = JSON.parse(notificationDataElement.textContent);
      if (
        notificationData &&
        notificationData.message &&
        notificationData.type
      ) {
        showNotification(notificationData.message, notificationData.type);
      }
      notificationDataElement.remove();
    } catch (e) {
      console.error("Gagal mem-parsing data notifikasi:", e);
    }
  }

  function showReportDetailsModal(data) {
    const detailsModal = document.getElementById("detailsModal");
    if (detailsModal) {
      const formatDate = (dateString) => {
        if (!dateString || dateString === "0000-00-00 00:00:00") return "N/A";
        return new Date(dateString).toLocaleString("id-ID", {
          year: "numeric",
          month: "long",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        });
      };
      detailsModal.querySelector(
        "#modal-title"
      ).textContent = `Laporan untuk ${data.nama_ups}`;
      detailsModal.querySelector("#modal-nama_ups").textContent = data.nama_ups;
      detailsModal.querySelector("#modal-lokasi").textContent = data.lokasi;
      detailsModal.querySelector("#modal-merk").textContent = data.merk;
      detailsModal.querySelector("#modal-tipe_ups").textContent = data.tipe_ups;
      detailsModal.querySelector("#modal-ukuran_kapasitas").textContent =
        data.ukuran_kapasitas;
      detailsModal.querySelector("#modal-jumlah_baterai").textContent =
        data.jumlah_baterai;
      detailsModal.querySelector("#modal-ip_address").textContent =
        data.ip_address;
      detailsModal.querySelector("#modal-perusahaan_maintenance").textContent =
        data.perusahaan_maintenance;
      let statusClass = "status-pending";
      if (data.status.toLowerCase().includes("selesai (terlambat)"))
        statusClass = "status-completed-late";
      else if (data.status.toLowerCase().includes("selesai"))
        statusClass = "status-completed";
      detailsModal.querySelector(
        "#modal-status"
      ).innerHTML = `<span class="status-badge ${statusClass}">${data.status}</span>`;
      let jenisClass = "";
      if (data.jenis.toLowerCase().includes("preventive"))
        jenisClass = "status-preventive";
      else if (data.jenis.toLowerCase().includes("corrective"))
        jenisClass = "status-corrective";
      detailsModal.querySelector(
        "#modal-jenis"
      ).innerHTML = `<span class="status-badge ${jenisClass}">${data.jenis}</span>`;
      detailsModal.querySelector("#modal-tanggal_jadwal").textContent =
        formatDate(data.tanggal_jadwal);
      detailsModal.querySelector("#modal-tanggal_pelaksanaan").textContent =
        formatDate(data.tanggal_pelaksanaan);
      detailsModal.querySelector("#modal-teknisi").textContent = data.teknisi;
      detailsModal.querySelector("#modal-hasil_pengecekan").innerHTML =
        (data.hasil_pengecekan || "").replace(/\n/g, "<br>") ||
        "Tidak ada temuan yang dilaporkan.";
      detailsModal.querySelector("#modal-pengubahan").innerHTML =
        (data.pengubahan || "").replace(/\n/g, "<br>") ||
        "Tidak ada tindakan yang dilaporkan.";
      const attachmentPath = data.attachment_path;
      const attachmentSection = detailsModal.querySelector(
        "#modal-attachment-section"
      );
      const attachmentBtn = detailsModal.querySelector(".view-attachment-btn");
      if (attachmentSection && attachmentBtn) {
        if (attachmentPath && attachmentPath.trim() !== "") {
          attachmentBtn.setAttribute("data-attachment-path", attachmentPath);
          attachmentSection.style.display = "block";
        } else {
          attachmentSection.style.display = "none";
        }
      }
      openModal(detailsModal);
    }
  }

  document.body.addEventListener("click", async function (e) {
    const target = e.target;
    const openReportModalBtn = target.closest(".open-report-modal-btn");
    if (openReportModalBtn) {
      const reportModal = document.getElementById("addReportModal");
      if (reportModal) {
        const data = openReportModalBtn.dataset;
        reportModal.querySelector("#report_maintenance_id").value =
          data.maintenanceId;
        reportModal.querySelector("#report_nama_ups").textContent =
          data.namaUps;
        reportModal.querySelector("#report_lokasi").textContent = data.lokasi;
        reportModal.querySelector("#report_jenis").textContent = data.jenis;
        reportModal.querySelector("#report_teknisi").value = data.teknisi;
        reportModal.querySelector("#report_tanggal_jadwal").value =
          data.tanggalJadwal;
        reportModal.querySelector("#report_old_attachment_path").value =
          data.attachmentPath;
        const scheduleDate = new Date(data.tanggalJadwal);
        reportModal.querySelector("#report_jadwal_display").textContent =
          scheduleDate.toLocaleString("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
          });
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById("report_tanggal_pelaksanaan").value = now
          .toISOString()
          .slice(0, 16);
        openModal(reportModal);
      }
    }

    const editReportBtn = target.closest(".edit-report-btn");
    if (editReportBtn) {
      const modal = document.getElementById("editReportModal");
      if (modal) {
        const data = editReportBtn.dataset;
        const updateCustomSelect = (selectId, value) => {
          const selectEl = modal.querySelector(selectId);
          if (!selectEl) return;
          const wrapper = selectEl.closest(".custom-select-wrapper");
          if (!wrapper) return;
          const triggerSpan = wrapper.querySelector(
            ".custom-select-trigger span"
          );
          selectEl.value = value;
          const selectedOption = selectEl.options[selectEl.selectedIndex];
          if (selectedOption && selectedOption.value) {
            triggerSpan.textContent = selectedOption.textContent;
            triggerSpan.classList.remove("placeholder");
          } else {
            const placeholder = wrapper.querySelector(
              ".custom-select-trigger .placeholder"
            );
            triggerSpan.textContent = placeholder
              ? placeholder.textContent
              : "-- Pilih --";
            triggerSpan.classList.add("placeholder");
          }
          wrapper.querySelectorAll(".custom-option").forEach((opt) => {
            opt.classList.remove("selected");
            if (opt.dataset.value == value) opt.classList.add("selected");
          });
        };
        const formatForInput = (dateString) => {
          if (!dateString || dateString === "0000-00-00 00:00:00") return "";
          const date = new Date(dateString);
          date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
          return date.toISOString().slice(0, 16);
        };
        const formatDateForDisplay = (dateString) => {
          if (!dateString || dateString === "0000-00-00 00:00:00") return "N/A";
          return new Date(dateString).toLocaleString("id-ID", {
            year: "numeric",
            month: "long",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
          });
        };
        modal.querySelector("#edit_maintenance_id").value =
          data.maintenance_id || "";
        modal.querySelector("#hidden_tanggal_jadwal").value =
          data.tanggal_jadwal || "";
        modal.querySelector("#edit-report-id").textContent = `#RL-${String(
          data.maintenance_id
        ).padStart(4, "0")}`;
        modal.querySelector("#edit_ups_name_display").textContent =
          data.nama_ups ? `${data.nama_ups} | ${data.lokasi}` : "--";
        modal.querySelector("#edit_teknisi_display").textContent =
          data.teknisi || "";
        modal.querySelector("#edit_tanggal_jadwal_display").textContent =
          formatDateForDisplay(data.tanggal_jadwal);
        modal.querySelector("#edit_jenis_display").textContent =
          data.jenis || "--";
        modal.querySelector("#edit_tanggal_pelaksanaan").value = formatForInput(
          data.tanggal_pelaksanaan
        );
        updateCustomSelect("#edit_status", data.status || "");
        modal.querySelector("#edit_catatan").value = data.catatan || "";
        modal.querySelector("#edit_hasil_pengecekan").value =
          data.hasil_pengecekan || "";
        modal.querySelector("#edit_pengubahan").value = data.pengubahan || "";
        modal.querySelector("#edit_old_attachment_path").value =
          data.attachment_path || "";
        const attachmentText = modal
          .querySelector("#edit_attachment")
          .parentElement.querySelector(".attachment-text");
        const attachmentBox = attachmentText.closest(".attachment-box");
        if (data.attachment_path) {
          attachmentText.textContent = data.attachment_path.split("/").pop();
        } else {
          attachmentText.textContent =
            attachmentBox.dataset.defaultText ||
            "Unggah file atau seret & jatuhkan";
        }
        initializeDynamicControls(modal);
        openModal(modal);
      }
    }

    if (target.closest("#addUpsBtn")) {
      e.preventDefault();
      openModal(document.getElementById("addUpsModal"));
    }

    const editBtn = target.closest(".edit-btn");
    if (editBtn) {
      e.preventDefault();
      const editModal = document.getElementById("editUpsModal");
      if (editModal) {
        const data = editBtn.dataset;
        editModal.querySelector("#edit_ups_id").value = data.ups_id;
        editModal.querySelector("#edit_nama_ups").value = data.nama_ups;
        editModal.querySelector("#edit_lokasi").value = data.lokasi;
        editModal.querySelector("#edit_merk").value = data.merk;
        editModal.querySelector("#edit_tipe_ups").value = data.tipe_ups;
        editModal.querySelector("#edit_ip_address").value = data.ip_address;
        editModal.querySelector("#edit_ukuran_kapasitas").value =
          data.ukuran_kapasitas;
        editModal.querySelector("#edit_jumlah_baterai").value =
          data.jumlah_baterai;
        editModal.querySelector("#edit_perusahaan_maintenance").value =
          data.perusahaan_maintenance;
        openModal(editModal);
      }
    }

    const detailsUpsBtn = target.closest(".open-ups-details-btn");
    if (detailsUpsBtn) {
      e.preventDefault();
      const detailsModal = document.getElementById("upsDetailsModal");
      if (detailsModal) {
        const data = detailsUpsBtn.dataset;
        detailsModal.dataset.upsId = data.ups_id;
        detailsModal.querySelector(
          "#ups-modal-title"
        ).textContent = `Detail untuk ${data.nama_ups}`;
        detailsModal.querySelector("#modal-ups-nama_ups").textContent =
          data.nama_ups;
        detailsModal.querySelector("#modal-ups-lokasi").textContent =
          data.lokasi;
        detailsModal.querySelector("#modal-ups-merk").textContent = data.merk;
        detailsModal.querySelector("#modal-ups-tipe_ups").textContent =
          data.tipe_ups;
        detailsModal.querySelector("#modal-ups-ukuran_kapasitas").textContent =
          data.ukuran_kapasitas ? `${data.ukuran_kapasitas} KVA` : "N/A";
        detailsModal.querySelector("#modal-ups-jumlah_baterai").textContent =
          data.jumlah_baterai;
        detailsModal.querySelector("#modal-ups-ip_address").textContent =
          data.ip_address;
        detailsModal.querySelector(
          "#modal-ups-perusahaan_maintenance"
        ).textContent = data.perusahaan_maintenance;
        detailsModal
          .querySelectorAll(".modal-tab")
          .forEach((tab) => tab.classList.remove("active"));
        detailsModal
          .querySelectorAll(".modal-tab-content")
          .forEach((content) => content.classList.remove("active"));
        detailsModal
          .querySelector('.modal-tab[data-tab="details"]')
          .classList.add("active");
        detailsModal.querySelector("#ups-details-tab").classList.add("active");
        detailsModal.querySelector("#report-detail-tab-button").style.display =
          "none";
        openModal(detailsModal);
        const historyContent = detailsModal.querySelector(
          "#ups-history-content"
        );
        historyContent.innerHTML =
          '<div class="spinner-container" style="text-align:center; padding: 40px;"><div class="spinner" style="margin:auto;"></div></div>';
        try {
          const response = await fetch(
            `${BASE_URL}/views/api/get_ups_history?ups_id=${data.ups_id}`
          );
          const historyData = await response.json();
          if (response.ok) {
            if (historyData.length > 0) {
              let tableHTML =
                '<table id="ups-history-table"><thead><tr><th>ID Laporan</th><th>Tanggal</th><th>Tipe</th><th>Status</th><th>Teknisi</th><th>Tindakan</th></tr></thead><tbody>';
              historyData.forEach((item) => {
                const reportId = `RL-${String(item.maintenance_id).padStart(
                  4,
                  "0"
                )}`;
                const date = new Date(
                  item.tanggal_pelaksanaan
                ).toLocaleDateString("id-ID", {
                  day: "2-digit",
                  month: "short",
                  year: "numeric",
                });
                let jenisClass = "";
                if (item.jenis.toLowerCase().includes("preventive"))
                  jenisClass = "status-preventive";
                else if (item.jenis.toLowerCase().includes("corrective"))
                  jenisClass = "status-corrective";
                const jenisText = item.jenis;
                let mainType = jenisText;
                let subType = "";
                const jenisMatch = jenisText.match(/^(.*?)\s*(\(.*\))$/);
                if (jenisMatch) {
                  mainType = jenisMatch[1].trim();
                  subType = `<span class="type-sub">${jenisMatch[2]}</span>`;
                }
                const jenisHtml = `<div class="maintenance-type-wrapper status-badge ${jenisClass}"><span class="type-main">${mainType}</span>${subType}</div>`;
                let statusClass = "";
                let statusText = item.status;
                let mainStatus = statusText;
                let subStatus = "";
                const statusMatch = statusText.match(/^(.*?)\s*(\(.*\))$/);
                if (statusMatch) {
                  mainStatus = statusMatch[1].trim();
                  subStatus = `<span class="type-sub">${statusMatch[2]}</span>`;
                }
                switch (item.status.toLowerCase()) {
                  case "selesai":
                    statusClass = "status-completed";
                    break;
                  case "selesai (terlambat)":
                    statusClass = "status-completed-late";
                    break;
                  default:
                    statusClass = "status-pending";
                    break;
                }
                const statusHtml = `<div class="maintenance-type-wrapper status-badge ${statusClass}"><span class="type-main">${mainStatus}</span>${subStatus}</div>`;
                let dataAttributes = "";
                for (const key in item) {
                  if (item[key] !== null) {
                    const sanitizedValue = String(item[key])
                      .replace(/'/g, "&apos;")
                      .replace(/"/g, "&quot;");
                    dataAttributes += `data-${key}='${sanitizedValue}' `;
                  }
                }
                tableHTML += `<tr><td>${reportId}</td><td>${date}</td><td>${jenisHtml}</td><td>${statusHtml}</td><td>${
                  item.teknisi || "N/A"
                }</td><td class="table-actions"><button class="btn-details open-details-btn" title="Lihat Detail" ${dataAttributes}><i class="fas fa-eye"></i></button></td></tr>`;
              });
              tableHTML += "</tbody></table>";
              historyContent.innerHTML = tableHTML;
            } else {
              historyContent.innerHTML =
                '<p style="text-align:center; padding: 20px;">Tidak ada riwayat pemeliharaan untuk unit ini.</p>';
            }
          } else {
            throw new Error(historyData.error || "Gagal memuat riwayat.");
          }
        } catch (error) {
          historyContent.innerHTML = `<p style="text-align:center; padding: 20px; color: #E74C3C;">Error: ${error.message}</p>`;
        }
      }
    }

    const modalTab = target.closest(".modal-tab");
    if (modalTab && target.closest("#upsDetailsModal")) {
      const modal = target.closest(".modal");
      const tabName = modalTab.dataset.tab;
      modal
        .querySelectorAll(".modal-tab")
        .forEach((tab) => tab.classList.remove("active"));
      modal
        .querySelectorAll(".modal-tab-content")
        .forEach((content) => content.classList.remove("active"));
      modalTab.classList.add("active");
      modal.querySelector(`#ups-${tabName}-tab`).classList.add("active");
    }

    const openDetailsBtn = target.closest(".open-details-btn");
    if (openDetailsBtn) {
      const data = openDetailsBtn.dataset;
      if (target.closest("#ups-history-content")) {
        const parentUpsModal = openDetailsBtn.closest("#upsDetailsModal");
        if (parentUpsModal) {
          const reportTab = parentUpsModal.querySelector(
            "#ups-report-detail-tab"
          );
          const formatDate = (dateString) => {
            if (!dateString || dateString === "0000-00-00 00:00:00")
              return "N/A";
            return new Date(dateString).toLocaleString("id-ID", {
              year: "numeric",
              month: "long",
              day: "numeric",
              hour: "2-digit",
              minute: "2-digit",
            });
          };
          reportTab.querySelector(".report-nama_ups").textContent =
            data.nama_ups || "";
          reportTab.querySelector(".report-lokasi").textContent =
            data.lokasi || "";
          reportTab.querySelector(".report-merk").textContent = data.merk || "";
          reportTab.querySelector(".report-tipe_ups").textContent =
            data.tipe_ups || "";
          reportTab.querySelector(".report-ukuran_kapasitas").textContent =
            data.ukuran_kapasitas ? `${data.ukuran_kapasitas} KVA` : "";
          reportTab.querySelector(".report-jumlah_baterai").textContent =
            data.jumlah_baterai || "";
          reportTab.querySelector(".report-ip_address").textContent =
            data.ip_address || "N/A";
          reportTab.querySelector(
            ".report-perusahaan_maintenance"
          ).textContent = data.perusahaan_maintenance || "";
          let statusClass = "status-pending";
          if (data.status.toLowerCase().includes("selesai (terlambat)"))
            statusClass = "status-completed-late";
          else if (data.status.toLowerCase().includes("selesai"))
            statusClass = "status-completed";
          reportTab.querySelector(
            ".report-status"
          ).innerHTML = `<span class="status-badge ${statusClass}">${data.status}</span>`;
          let jenisClass = "";
          if (data.jenis.toLowerCase().includes("preventive"))
            jenisClass = "status-preventive";
          else if (data.jenis.toLowerCase().includes("corrective"))
            jenisClass = "status-corrective";
          reportTab.querySelector(
            ".report-jenis"
          ).innerHTML = `<span class="status-badge ${jenisClass}">${data.jenis}</span>`;
          reportTab.querySelector(".report-tanggal_jadwal").textContent =
            formatDate(data.tanggal_jadwal);
          reportTab.querySelector(".report-tanggal_pelaksanaan").textContent =
            formatDate(data.tanggal_pelaksanaan);
          reportTab.querySelector(".report-teknisi").textContent =
            data.teknisi || "N/A";
          reportTab.querySelector(".report-hasil_pengecekan").innerHTML =
            (data.hasil_pengecekan || "").replace(/\n/g, "<br>") ||
            "Tidak ada temuan.";
          reportTab.querySelector(".report-pengubahan").innerHTML =
            (data.pengubahan || "").replace(/\n/g, "<br>") ||
            "Tidak ada tindakan.";
          const attachmentSection = reportTab.querySelector(
            ".report-attachment-section"
          );
          const attachmentBtn = attachmentSection.querySelector(
            ".view-attachment-btn"
          );
          if (data.attachment_path && data.attachment_path.trim() !== "") {
            attachmentBtn.setAttribute(
              "data-attachment-path",
              data.attachment_path
            );
            attachmentSection.style.display = "block";
          } else {
            attachmentSection.style.display = "none";
          }
          const reportTabButton = parentUpsModal.querySelector(
            "#report-detail-tab-button"
          );
          parentUpsModal
            .querySelectorAll(".modal-tab")
            .forEach((tab) => tab.classList.remove("active"));
          parentUpsModal
            .querySelectorAll(".modal-tab-content")
            .forEach((content) => content.classList.remove("active"));
          reportTabButton.style.display = "inline-flex";
          reportTabButton.classList.add("active");
          reportTab.classList.add("active");
        }
      } else {
        showReportDetailsModal(data);
      }
    }

    const viewAttachmentBtn = target.closest(".view-attachment-btn");
    if (viewAttachmentBtn) {
      e.preventDefault();
      const attachmentModal = document.getElementById("attachmentViewerModal");
      if (attachmentModal) {
        const filePath = viewAttachmentBtn.getAttribute("data-attachment-path");
        if (filePath) {
          const attachmentFrame = document.getElementById("attachment-iframe");
          const attachmentTitle = document.getElementById(
            "attachment-viewer-title"
          );
          attachmentTitle.textContent = `Lampiran: ${filePath
            .split("/")
            .pop()}`;
          attachmentFrame.src = "about:blank"; // Clear previous content
          openModal(attachmentModal);

          // Show a temporary loading message
          const doc = attachmentFrame.contentDocument;
          doc.open();
          doc.write(
            `<body style="font-family: sans-serif; color: #555; display:flex; align-items:center; justify-content:center;">Memuat pratinjau...</body>`
          );
          doc.close();

          try {
            const response = await fetch(
              `${BASE_URL}/views/hasil/view_attachment?file=${encodeURIComponent(
                filePath
              )}`
            );

            // Check if the response is OK, otherwise parse the JSON error
            if (!response.ok) {
              const errorData = await response.json().catch(() => ({
                error: `HTTP error! Status: ${response.status}`,
              }));
              throw new Error(
                errorData.error || `HTTP error! Status: ${response.status}`
              );
            }

            const data = await response.json();
            if (data.error) throw new Error(data.error);

            const dataUrl = `data:${data.mimeType};base64,${data.data}`;

            // REVISED: Use a specific method for images to ensure they render correctly,
            // and the direct src method for PDFs and other embeddable types.
            if (data.mimeType.startsWith("image/")) {
              const imageDoc = attachmentFrame.contentDocument;
              imageDoc.open();
              imageDoc.write(
                `<body style="margin:0; background-color:#f0f0f0; display:flex; align-items:center; justify-content:center; height: 100vh; overflow: hidden;"><img src="${dataUrl}" style="max-width:100%; max-height:100%; object-fit:contain;"></body>`
              );
              imageDoc.close();
            } else {
              attachmentFrame.src = dataUrl;
            }
          } catch (error) {
            const errorDoc = attachmentFrame.contentDocument;
            errorDoc.open();
            errorDoc.write(
              `<p style="padding: 20px; color: #333; font-family: sans-serif;">Tidak dapat memuat pratinjau file: ${error.message}</p>`
            );
            errorDoc.close();
          }
        }
      }
    }

    const deleteBtn = target.closest(".delete-btn");
    if (deleteBtn) {
      e.preventDefault();
      const deleteModal = document.getElementById("deleteConfirmModal");
      if (deleteModal) {
        const detailText = deleteBtn.getAttribute("data-detail") || "item ini";
        deleteModal.querySelector(
          "#deleteModalDetail"
        ).innerHTML = `Apakah Anda benar-benar yakin ingin menghapus: <strong>${detailText}</strong>? Proses ini tidak bisa dibatalkan.`;
        deleteModal.querySelector("#confirmDeleteBtn").href =
          deleteBtn.getAttribute("data-href");
        openModal(deleteModal);
      }
    }

    const excelBtn = target.closest(".export-single-excel-btn");
    if (excelBtn) {
      const data = excelBtn.dataset;
      const formatDate = (dateString) => {
        if (!dateString || dateString === "0000-00-00 00:00:00") return "";
        return new Date(dateString).toLocaleString("id-ID");
      };
      const headers = [
        "No. Laporan",
        "Nama UPS",
        "Lokasi",
        "Merk",
        "Tipe",
        "Kapasitas (KVA)",
        "Jml Baterai",
        "Alamat IP",
        "Departemen",
        "Tgl Jadwal",
        "Tgl Pelaksanaan",
        "Status",
        "Jenis Pemeliharaan",
        "Teknisi",
        "Hasil Pengecekan",
        "Penggantian / Tindakan",
      ];
      const rowData = [
        `RL-${String(data.maintenance_id).padStart(4, "0")}`,
        data.nama_ups,
        data.lokasi,
        data.merk,
        data.tipe_ups,
        `${data.ukuran_kapasitas} KVA`,
        data.jumlah_baterai,
        data.ip_address,
        data.perusahaan_maintenance,
        formatDate(data.tanggal_jadwal),
        formatDate(data.tanggal_pelaksanaan),
        data.status,
        (data.jenis || "") + (data.sub_jenis ? ` ${data.sub_jenis}` : ""),
        data.teknisi,
        data.hasil_pengecekan,
        data.pengubahan,
      ];
      let csvContent = headers.join(";") + "\r\n";
      csvContent += rowData.map(escapeCsvCell).join(";") + "\r\n";
      const blob = new Blob(["\uFEFF" + csvContent], {
        type: "text/csv;charset=utf-8;",
      });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = `Riwayat|RL-${String(data.maintenance_id).padStart(
        4,
        "0"
      )}.csv`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }

    const pdfBtn = target.closest(".export-single-pdf-btn");
    if (pdfBtn) {
      const data = pdfBtn.dataset;
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      const reportId = `RL-${String(data.maintenance_id).padStart(4, "0")}`;
      const formatDate = (dateString) => {
        if (!dateString || dateString === "0000-00-00 00:00:00") return "N/A";
        return new Date(dateString).toLocaleString("id-ID", {
          year: "numeric",
          month: "long",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        });
      };
      doc.setFontSize(18);
      doc.text(`Laporan Pemeliharaan: ${reportId}`, 14, 22);
      doc.autoTable({
        startY: 30,
        theme: "grid",
        head: [["Informasi UPS", ""]],
        body: [
          ["Nama UPS", data.nama_ups],
          ["Lokasi", data.lokasi],
          ["Merk / Tipe", `${data.merk} / ${data.tipe_ups}`],
          ["Kapasitas", `${data.ukuran_kapasitas} KVA`],
          ["Departemen", data.perusahaan_maintenance],
        ],
        headStyles: { fillColor: [41, 128, 185] },
        columnStyles: {
          0: { fontStyle: "bold", cellWidth: 50, halign: "left" },
          1: { cellWidth: 130, halign: "left" },
        },
      });
      let finalY = doc.lastAutoTable.finalY;
      const jenisText =
        data.jenis + (data.sub_jenis ? ` ${data.sub_jenis}` : "");
      doc.autoTable({
        startY: finalY + 5,
        theme: "grid",
        head: [["Detail Laporan", ""]],
        body: [
          ["Tanggal Pelaksanaan", formatDate(data.tanggal_pelaksanaan)],
          ["Teknisi", data.teknisi],
          ["Status", data.status],
          ["Jenis Pemeliharaan", jenisText],
        ],
        headStyles: { fillColor: [41, 128, 185] },
        columnStyles: {
          0: { fontStyle: "bold", cellWidth: 50, halign: "left" },
          1: { cellWidth: 130, halign: "left" },
        },
      });

      finalY = doc.lastAutoTable.finalY;

      const addTextBlock = (title, text, startY) => {
        let currentY = startY;
        const maxWidth = 180;
        const margin = 14;

        doc.setFont("helvetica", "normal");
        doc.text(title, margin, currentY);
        currentY += 6;

        doc.setFont("helvetica", "normal");
        const lines = doc.splitTextToSize(text || "Tidak ada data.", maxWidth);
        doc.text(lines, margin, currentY);

        return currentY + doc.getTextDimensions(lines).h;
      };

      doc.setFontSize(14);
      finalY = addTextBlock(
        "Hasil Pengecekan:",
        data.hasil_pengecekan,
        finalY + 10
      );

      doc.setFontSize(14);
      finalY = addTextBlock(
        "Tindakan yang diambil:",
        data.pengubahan,
        finalY + 4
      );

      doc.save(`Riwayat|${reportId}.pdf`);
    }

    const modal = target.closest(".modal");
    if (modal) {
      if (
        target.matches(
          ".close-modal-btn, .close-modal-footer-btn, #cancelDeleteBtn"
        ) ||
        target === modal
      ) {
        e.preventDefault();
        if (modal.id === "attachmentViewerModal") {
          modal.querySelector("iframe").src = "about:blank";
        }
        closeModal(modal);
      }
    }
  });

  function fetchAndUpdateContent(url, container) {
    const loadingOverlay = container.querySelector(".loading-overlay");
    if (loadingOverlay) loadingOverlay.classList.add("active");

    const displayUrl = new URL(url);
    displayUrl.searchParams.delete("ajax_reports");
    displayUrl.searchParams.delete("ajax_ups_list");

    fetch(url.toString())
      .then((response) => {
        if (!response.ok)
          throw new Error("Network response was not ok for: " + url.toString());
        return response.text();
      })
      .then((html) => {
        const contentWrapper =
          container.querySelector("#ups-list-content") || container;
        contentWrapper.innerHTML = html;

        // REVISED: Re-initialize controls on the container that was updated.
        initializeDynamicControls(container);

        history.pushState(
          { path: displayUrl.pathname },
          "",
          displayUrl.pathname
        );
      })
      .catch((error) => {
        console.error("Kesalahan AJAX:", error);
        showNotification("Gagal memuat konten.", "error");
      })
      .finally(() => {
        // REVISED: Ensure the loading overlay is always removed
        if (loadingOverlay) loadingOverlay.classList.remove("active");
      });
  }

  function initAjaxControls(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.addEventListener("click", function (e) {
      const pagLink = e.target.closest(".pagination a:not(.disabled)");
      if (pagLink) {
        e.preventDefault();
        const ajaxParam =
          containerId === "reports-section-container"
            ? "ajax_reports=1"
            : "ajax_ups_list=1";
        const url = `${pagLink.href}&${ajaxParam}`;
        fetchAndUpdateContent(url, container);
      }
    });

    container.addEventListener("submit", function (e) {
      if (e.target.matches("form.filter-form")) {
        e.preventDefault();
        const formData = new URLSearchParams(new FormData(e.target));
        const ajaxParam =
          containerId === "reports-section-container"
            ? "ajax_reports=1"
            : "ajax_ups_list=1";
        const url = `${e.target.action}?${formData.toString()}&${ajaxParam}`;
        fetchAndUpdateContent(url, container);
      }
    });
  }

  initAjaxControls("reports-section-container");
  initAjaxControls("ups-list-container");

  const miniCalendarContainer = document.getElementById(
    "mini-calendar-container"
  );
  if (miniCalendarContainer) {
    const monthYearEl = document.getElementById("mini-calendar-month-year");
    const daysGridEl = document.getElementById("mini-calendar-days-grid");
    let miniCalendarDate = new Date();

    const renderMiniCalendar = async (date) => {
      if (!monthYearEl || !daysGridEl) return;

      const year = date.getFullYear();
      const month = date.getMonth();
      monthYearEl.textContent = date.toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
      });

      // Display a loading spinner while fetching data
      daysGridEl.innerHTML =
        '<div class="spinner-container" style="grid-column: span 7; text-align:center; padding: 40px;"><div class="spinner" style="margin:auto;"></div></div>';

      try {
        const response = await fetch(
          `${BASE_URL}/views/api/get_calendar_data?year=${year}&month=${
            month + 1
          }`
        );
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        const events = await response.json();
        const eventsByDate = events.reduce((acc, event) => {
          (acc[event.date] = acc[event.date] || []).push(event);
          return acc;
        }, {});

        // Clear the loading spinner
        daysGridEl.innerHTML = "";

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date();

        // Add padding for days of the previous month
        const daysInPrevMonth = new Date(year, month, 0).getDate();
        for (let i = firstDayOfMonth; i > 0; i--) {
          daysGridEl.innerHTML += `<div class="mini-calendar-day other-month">${
            daysInPrevMonth - i + 1
          }</div>`;
        }

        // Render the days of the current month
        for (let day = 1; day <= daysInMonth; day++) {
          const dayEl = document.createElement("div");
          dayEl.className = "mini-calendar-day";
          const dateString = `${year}-${String(month + 1).padStart(
            2,
            "0"
          )}-${String(day).padStart(2, "0")}`;
          if (
            year === today.getFullYear() &&
            month === today.getMonth() &&
            day === today.getDate()
          ) {
            dayEl.classList.add("today");
          }
          if (eventsByDate[dateString]) {
            dayEl.classList.add("has-event");
          }
          dayEl.textContent = day;
          daysGridEl.appendChild(dayEl);
        }

        // Add padding for days of the next month
        const gridDaysCount = firstDayOfMonth + daysInMonth;
        const nextMonthDays = (7 - (gridDaysCount % 7)) % 7;
        for (let i = 1; i <= nextMonthDays; i++) {
          daysGridEl.innerHTML += `<div class="mini-calendar-day other-month">${i}</div>`;
        }
      } catch (error) {
        console.error("Gagal memuat data kalender mini:", error);
        daysGridEl.innerHTML =
          '<p style="grid-column: span 7; text-align:center; color: #E74C3C; padding: 10px;">Gagal memuat event.</p>';
      }
    };
    renderMiniCalendar(miniCalendarDate);
  }

  const openCalendarModalBtn = document.getElementById("openCalendarModalBtn");
  const fullCalendarModal = document.getElementById("fullCalendarModal");
  if (openCalendarModalBtn && fullCalendarModal) {
    const monthYearEl = document.getElementById("modal-calendar-month-year");
    const daysGridEl = document.getElementById("modal-calendar-days-grid");
    const prevMonthBtn = document.getElementById("modal-prev-month-btn");
    const nextMonthBtn = document.getElementById("modal-next-month-btn");
    const eventModal = document.getElementById("fullCalendarEventModal");
    const eventModalTitle = document.getElementById("full-event-modal-title");
    let currentDate = new Date();
    const renderFullCalendar = async (date) => {
      const year = date.getFullYear();
      const month = date.getMonth();
      monthYearEl.textContent = date.toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
      });
      const response = await fetch(
        `${BASE_URL}/views/api/get_calendar_data?year=${year}&month=${
          month + 1
        }`
      );
      const events = await response.json();
      const eventsByDate = events.reduce((acc, event) => {
        (acc[event.date] = acc[event.date] || []).push(event);
        return acc;
      }, {});
      daysGridEl.innerHTML = "";
      const firstDayOfMonth = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const today = new Date();
      for (let i = 0; i < firstDayOfMonth; i++) {
        daysGridEl.innerHTML += `<div class="calendar-day other-month"></div>`;
      }
      for (let day = 1; day <= daysInMonth; day++) {
        const dayEl = document.createElement("div");
        dayEl.className = "calendar-day";
        const dateString = `${year}-${String(month + 1).padStart(
          2,
          "0"
        )}-${String(day).padStart(2, "0")}`;
        if (
          year === today.getFullYear() &&
          month === today.getMonth() &&
          day === today.getDate()
        ) {
          dayEl.classList.add("today");
        }
        let eventsHtml = "";
        if (eventsByDate[dateString]) {
          eventsByDate[dateString].forEach((event) => {
            const eventEl = document.createElement("div");
            eventEl.className = `calendar-event ${event.class}`;
            eventEl.textContent = event.title;
            eventEl.dataset.details = JSON.stringify(event.details);
            eventEl.dataset.title = event.title;
            eventsHtml += eventEl.outerHTML;
          });
        }
        dayEl.innerHTML = `<div class="day-number">${day}</div><div class="calendar-events-list">${eventsHtml}</div>`;
        daysGridEl.appendChild(dayEl);
      }
    };
    openCalendarModalBtn.addEventListener("click", () => {
      currentDate = new Date();
      renderFullCalendar(currentDate);
      openModal(fullCalendarModal);
    });
    prevMonthBtn.addEventListener("click", (e) => {
      e.preventDefault();
      currentDate.setMonth(currentDate.getMonth() - 1);
      renderFullCalendar(currentDate);
    });
    nextMonthBtn.addEventListener("click", (e) => {
      e.preventDefault();
      currentDate.setMonth(currentDate.getMonth() + 1);
      renderFullCalendar(currentDate);
    });
    daysGridEl.addEventListener("click", function (e) {
      const eventEl = e.target.closest(".calendar-event");
      if (eventEl) {
        const details = JSON.parse(eventEl.dataset.details);
        const title = eventEl.dataset.title;
        eventModalTitle.textContent = title;
        document.getElementById("cal-modal-nama_ups").textContent =
          details.nama_ups || "N/A";
        document.getElementById("cal-modal-lokasi").textContent =
          details.lokasi || "N/A";
        document.getElementById("cal-modal-merk").textContent =
          details.merk || "N/A";
        document.getElementById("cal-modal-tipe_ups").textContent =
          details.tipe_ups || "N/A";
        document.getElementById("cal-modal-ukuran_kapasitas").textContent =
          details.ukuran_kapasitas ? `${details.ukuran_kapasitas} KVA` : "N/A";
        document.getElementById("cal-modal-jumlah_baterai").textContent =
          details.jumlah_baterai || "N/A";
        document.getElementById("cal-modal-ip_address").textContent =
          details.ip_address || "N/A";
        document.getElementById(
          "cal-modal-perusahaan_maintenance"
        ).textContent = details.perusahaan_maintenance || "N/A";
        document.getElementById("cal-modal-tanggal_jadwal").textContent =
          details.tanggal_jadwal || "N/A";
        document.getElementById("cal-modal-teknisi").textContent =
          details.teknisi || "N/A";
        let statusClass = "status-scheduled";
        let statusText = details.status || "Scheduled";
        if (statusText.toLowerCase().includes("selesai (terlambat)"))
          statusClass = "status-completed-late";
        else if (statusText.toLowerCase().includes("selesai"))
          statusClass = "status-completed";
        else if (statusText.toLowerCase().includes("ditunda"))
          statusClass = "status-pending";
        document.getElementById(
          "cal-modal-status"
        ).innerHTML = `<span class="status-badge ${statusClass}">${statusText}</span>`;
        let jenisClass = "";
        let jenisText = details.jenis || "";
        if (jenisText.toLowerCase().includes("preventive"))
          jenisClass = "status-preventive";
        else if (jenisText.toLowerCase().includes("corrective"))
          jenisClass = "status-corrective";
        document.getElementById(
          "cal-modal-jenis"
        ).innerHTML = `<span class="status-badge ${jenisClass}">${jenisText}</span>`;

        // REVISED: Handle attachment button visibility
        const attachmentSection = document.getElementById(
          "cal-modal-attachment-section"
        );
        const attachmentBtn = attachmentSection.querySelector(
          ".view-attachment-btn"
        );
        if (details.attachment_path && details.attachment_path.trim() !== "") {
          attachmentBtn.setAttribute(
            "data-attachment-path",
            details.attachment_path
          );
          attachmentSection.style.display = "block";
        } else {
          attachmentSection.style.display = "none";
        }

        openModal(eventModal);
      }
    });
  }

  const donutChart = document.querySelector(".donut-chart");
  const legendItems = document.querySelectorAll(".donut-legend li");
  if (
    donutChart &&
    legendItems.length > 0 &&
    typeof donutStatusData !== "undefined"
  ) {
    const originalGradient = donutChart.style.background;
    legendItems.forEach((item) => {
      item.addEventListener("mouseenter", (e) => {
        const hoveredStatus = e.currentTarget.dataset.status;
        let currentPercentage = 0;
        const gradientParts = donutStatusData.map((statusInfo) => {
          const statusKey = statusInfo.status.toLowerCase();
          const percentage =
            totalStatusCount > 0
              ? (statusInfo.count / totalStatusCount) * 100
              : 0;
          const color = donutStatusColors[statusKey] || "#e0e0e0";
          const finalColor = statusKey === hoveredStatus ? color : "#e9ecef";
          const part = `${finalColor} ${currentPercentage}% ${
            currentPercentage + percentage
          }%`;
          currentPercentage += percentage;
          return part;
        });
        donutChart.style.background = `conic-gradient(${gradientParts.join(
          ", "
        )})`;
        donutChart.classList.add("is-hovered");
      });
      item.addEventListener("mouseleave", () => {
        donutChart.style.background = originalGradient;
        donutChart.classList.remove("is-hovered");
      });
    });
  }

  // REVISED: Footer Toggle now targets the new wrapper
  const footerToggleBtn = document.getElementById("footer-toggle-btn");
  const siteFooterWrapper = document.getElementById("site-footer");
  const footerStateKey = "footerState";

  const applyFooterState = (state) => {
    if (!siteFooterWrapper) return;
    const isCollapsed = state === "collapsed";
    siteFooterWrapper.classList.toggle("footer-collapsed", isCollapsed);
  };

  // On page load, apply the saved state or a default
  const savedFooterState = localStorage.getItem(footerStateKey);
  applyFooterState(savedFooterState || "expanded");

  if (footerToggleBtn) {
    footerToggleBtn.addEventListener("click", () => {
      const isCurrentlyCollapsed =
        siteFooterWrapper.classList.contains("footer-collapsed");
      const newState = isCurrentlyCollapsed ? "expanded" : "collapsed";
      applyFooterState(newState);
      localStorage.setItem(footerStateKey, newState);
    });
  }

  // REVISED: New, more robust handler for the view switcher on the dashboard
  const reportsContainer = document.getElementById("reports-section-container");
  if (reportsContainer) {
    // Use event delegation for the view mode change
    reportsContainer.addEventListener("change", (e) => {
      if (e.target.name === "view_mode") {
        const newView = e.target.value;
        const url = new URL(window.location);
        const currentPage = url.searchParams.get("page") || "1";

        // Update the 'view' parameter in the URL and preserve the page
        url.searchParams.set("view", newView);
        url.searchParams.set("page", currentPage);

        // Update all pagination links on the page to preserve the new view state
        reportsContainer.querySelectorAll(".pagination a").forEach((link) => {
          try {
            const linkUrl = new URL(link.href);
            linkUrl.searchParams.set("view", newView);
            link.href = linkUrl.toString();
          } catch (error) {
            console.error("Could not update pagination link:", link, error);
          }
        });

        // Update the browser's URL bar without reloading the page
        window.history.pushState({}, "", url.pathname);

        // Visually switch between table and card views
        const tableContainer =
          reportsContainer.querySelector(".table-container");
        const cardContainer = reportsContainer.querySelector(
          ".report-cards-wrapper"
        );

        if (tableContainer && cardContainer) {
          tableContainer.classList.toggle("view-hidden", newView !== "table");
          cardContainer.classList.toggle("view-hidden", newView !== "card");
        }
      }
    });
  }
});
