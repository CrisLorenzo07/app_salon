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
  const summary = document.querySelector(".summary-details");
  const summaryHeading = document.querySelector("#summary-heading");
  summary.replaceChildren();
  document.querySelector(".summary-content .alert")?.remove();

  if (
    Object.values(appointment).includes("") ||
    appointment.services.length === 0
  ) {
    summaryHeading.hidden = false;
    showAlert(
      "Faltan datos de Servicios, Fecha u Hora",
      "error",
      ".summary-content",
      false,
    );
    return;
  }

  summaryHeading.hidden = true;

  const { name, date, time, services } = appointment;

  const appointmentHeading = document.createElement("h3");
  appointmentHeading.textContent = "Resumen de Cita";
  summary.appendChild(appointmentHeading);

  const nameCustomer = document.createElement("P");
  const nameLabel = document.createElement("span");
  nameLabel.textContent = "Nombre:";
  nameCustomer.append(nameLabel, ` ${name}`);

  const objDate = new Date(date);
  const month = objDate.getMonth();
  const day = objDate.getDate() + 2;
  const year = objDate.getFullYear();

  const utcDate = new Date(Date.UTC(year, month, day));

  const options = {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  };
  const formatedDate = utcDate.toLocaleDateString("es-UY", options);

  const appointmentDate = document.createElement("P");
  appointmentDate.innerHTML = `<span>Fecha:</span> ${formatedDate}`;

  const appointmentTime = document.createElement("P");
  appointmentTime.innerHTML = `<span>Hora:</span> ${time} Horas`;

  summary.appendChild(nameCustomer);
  summary.appendChild(appointmentDate);
  summary.appendChild(appointmentTime);

  const servicesHeading = document.createElement("h3");
  servicesHeading.textContent = "Resumen de Servicios";
  servicesHeading.classList.add("services-heading");
  summary.appendChild(servicesHeading);

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
  });

  const reserveButton = document.createElement("BUTTON");
  reserveButton.type = "button";
  reserveButton.classList.add("button");
  reserveButton.textContent = "Reservar Cita";
  reserveButton.onclick = reserveAppointment;

  summary.appendChild(reserveButton);
}

function reserveAppointment() {
  console.log("Reservando cita....");
}
