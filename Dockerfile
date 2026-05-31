FROM maven:3.8.4-openjdk-11-slim AS build
WORKDIR /app
COPY GlobeTrekWeb/pom.xml .
COPY GlobeTrekWeb/src ./src
RUN mvn clean package

FROM tomcat:9.0-jdk11-openjdk

# Change Tomcat default port from 8080 to 7860 for Hugging Face Spaces compatibility
RUN sed -i 's/port="8080"/port="7860"/g' /usr/local/tomcat/conf/server.xml

# Ensure that all directories are fully writable by user 1000 (Hugging Face default non-root runner)
RUN chmod -R 777 /usr/local/tomcat

COPY --from=build /app/target/*.war /usr/local/tomcat/webapps/ROOT.war

EXPOSE 7860
CMD ["catalina.sh", "run"]
