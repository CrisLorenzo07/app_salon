let currentStep = 1;

const appointment = {
  name: "",
  date: "",
  time: "",
  services: [],
};

document.addEventListener("DOMContentLoaded", function () {
  initApp();
});

function initApp() {
  showSection();
  initTabs();
  updatePaginationButtons();
  initNextButton();
  initPreviousButton();

  fetchServices();

  customerName();
  selectedDate();
  selectedTime();
}

function showSection() {
  const previousSection = document.querySelector(".is-visible");
  if (previousSection) {
    previousSection.classList.remove("is-visible");
  }

  const section = document.querySelector(`#step-${currentStep}`);
  section.classList.add("is-visible");

  const previousTab = document.querySelector(".tabs button.is-active");
  if (previousTab) {
    previousTab.classList.remove("is-active");
  }

  const tab = document.querySelector(
    `.tabs button[data-step="${currentStep}"]`,
  );
  tab.classList.add("is-active");

  if (currentStep === 3) {
    showSummary();
  }
}

function initTabs() {
  const buttons = document.querySelectorAll(".tabs button");

  buttons.forEach((button) => {
    button.addEventListener("click", function (e) {
      currentStep = parseInt(e.target.dataset.step);
      showSection();
      updatePaginationButtons();
    });
  });
}

function updatePaginationButtons() {
  const previousButton = document.querySelector("#previous");
  const nextButton = document.querySelector("#next");

  if (currentStep === 1) {
    previousButton.classList.add("is-hidden");
    nextButton.classList.remove("is-hidden");
  } else if (currentStep === 3) {
    previousButton.classList.remove("is-hidden");
    nextButton.classList.add("is-hidden");
  } else {
    previousButton.classList.remove("is-hidden");
    nextButton.classList.remove("is-hidden");
  }
}

function initPreviousButton() {
  const previousButton = document.querySelector("#previous");
  previousButton.addEventListener("click", function () {
    if (currentStep <= 1) return;
    currentStep--;
    showSection();
    updatePaginationButtons();
  });
}

function initNextButton() {
  const nextButton = document.querySelector("#next");
  nextButton.addEventListener("click", function () {
    if (currentStep >= 3) return;
    currentStep++;
    showSection();
    updatePaginationButtons();
  });
}

async function fetchServices() {
  try {
    const url = "/api/services";
    const response = await fetch(url);
    const services = await response.json();
    renderServices(services);
  } catch (error) {
    console.log(error);
  }
}

function renderServices(services) {
  services.forEach((service) => {
    const { id, name, price } = service;

    const serviceName = document.createElement("P");
    serviceName.classList.add("service-name");
    serviceName.textContent = name;

    const servicePrice = document.createElement("P");
    servicePrice.classList.add("service-price");
    servicePrice.textContent = `$${price.toFixed(2)}`;

    const serviceElement = document.createElement("DIV");
    serviceElement.classList.add("service");
    serviceElement.dataset.serviceId = id;
    serviceElement.onclick = function () {
      selectService(service);
    };

    serviceElement.appendChild(serviceName);
    serviceElement.appendChild(servicePrice);

    document.querySelector("#services").appendChild(serviceElement);
  });
}

function selectService(service) {
  const { id } = service;
  const { services } = appointment;

  const serviceElement = document.querySelector(`[data-service-id="${id}"]`);

  if (services.some((addedService) => addedService.id === id)) {
    appointment.services = services.filter(
      (addedService) => addedService.id !== id,
    );
    serviceElement?.classList.remove("selected");
  } else {
    services.push(service);
    serviceElement?.classList.add("selected");
  }
}

function customerName() {
  appointment.name = document.querySelector("#name").value;
}

function selectedDate() {
  const inputDate = document.querySelector("#date");
  inputDate.addEventListener("input", function (e) {
    const day = new Date(e.target.value).getUTCDay();
    if ([6, 0].includes(day)) {
      e.target.value = "";
      appointment.date = "";
      showAlert("Fin de semanas se encuetra cerrado", "error", ".form");
    } else {
      appointment.date = e.target.value;
    }
  });
}

function selectedTime() {
  const inputTime = document.querySelector("#time");
  inputTime.addEventListener("input", function (e) {
    console.log(e.target.value);

    const appointmentTime = e.target.value;
    const time = appointmentTime.split(":")[0];
    if (time < 9 || time > 19) {
      e.target.value = "";
      appointment.time = "";
      showAlert("Hora no Válida", "error", ".form");
    } else {
      appointment.time = e.target.value;
    }
  });
}

function showAlert(message, type, element, disappears = true) {
  const reference = document.querySelector(element);
  if (!reference) return;

  const previousAlert = reference.querySelector(".alert");
  previousAlert?.remove();

  const alert = document.createElement("DIV");
  alert.textContent = message;
  alert.classList.add("alert");
  alert.classList.add(type);

  reference.appendChild(alert);

  if (disappears) {
    setTimeout(() => {
      alert.remove();
    }, 3000);
  }
}

function showSummary() {
  if (
    Object.values(appointment).includes("") ||
    appointment.services.length === 0
  ) {
    showAlert(
      "Faltan datos de Servicios, Fecha u Hora",
      "error",
      ".summary-content",
      false,
    );
    return;
  }

  const { name, date, time, services } = appointment;

  const nameCustomer = document.createElement("P");
  nameCustomer.innerHTML = `<span>Nombre:</span> ${name}`;

  const appointmentDate = document.createElement("P");
  appointmentDate.innerHTML = `<span>Fecha:</span> ${date}`;

  const appointmentTime = document.createElement("P");
  appointmentTime.innerHTML = `<span>Hora:</span> ${time}`;

  const summary = document.querySelector(".summary-content");
  summary.appendChild(nameCustomer);
  summary.appendChild(appointmentDate);
  summary.appendChild(appointmentTime);

  services.forEach((service) => {
    const { name, price } = service;
    const serviceContainer = document.createElement("DIV");
    serviceContainer.classList.add("service-content");

    const serviceText = document.createElement("P");
    serviceText.textContent = name;

    const servicePrice = document.createElement("P");
    servicePrice.innerHTML = `<span>Precio:</span> $${price.toFixed(2)}`;

    serviceContainer.appendChild(serviceText);
    serviceContainer.appendChild(servicePrice);
    summary.appendChild(serviceContainer);

    /*    document.querySelector(".summary-content").appendChild(customerName);
    document.querySelector(".summary-content").appendChild(appointmentDate);
    document.querySelector(".summary-content").appendChild(appointmentTime);

    document.querySelector(".summary-content").appendChild(serviceText);
    document.querySelector(".summary-content").appendChild(servicePrice); */
  });
}
